<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Rdf;

use DataValues\StringValue;
use MediaWiki\Extension\WikibaseBoolean\BooleanStrings;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\Repo\Rdf\ValueSnakRdfBuilder;
use Wikimedia\Purtle\RdfWriter;

/**
 * Maps a boolean-valued snak to RDF for the Wikibase Query Service (and any
 * other RDF export), as xsd:boolean.
 *
 * SUPERSEDED (2026-09-18): this class used to read a native
 * DataValues\BooleanValue and branch on `$value->getValue()` truthiness
 * directly (`$value->getValue() ? 'true' : 'false'`). See
 * manuals/adr/0006-boolean-as-string-value-type.md for why the underlying
 * value is now a DataValues\StringValue instead -- in short, a real
 * Wikibase-core bug meant a BooleanValue could never survive being edited
 * through the UI at all.
 *
 * That switch reintroduces, in PHP, the exact shape of bug the ADR is
 * about in JS: `$value->getValue()` is now a STRING, and in PHP, just as
 * in JS, any non-empty string is truthy -- including the string "false".
 * `$value->getValue() ? 'true' : 'false'` would therefore emit the
 * literal RDF "true" for every stored value, including ones that say
 * "false", which would have been a silent, serious correctness bug in
 * exactly the code this class's original docblock was written to guard
 * against. This version compares the string by exact equality instead
 * (`$rawValue === BooleanStrings::TRUE`), which is immune to that: it
 * only ever emits "true" for the literal string "true", and normalizes
 * anything else -- "false" included, but also any unexpected value that
 * shouldn't be reachable if BooleanValidator has done its job -- to
 * "false", rather than trusting truthiness or throwing mid-RDF-export.
 *
 * CRITICAL correctness note, carried over unchanged from the original
 * scaffold and still the reason this class exists at all: PHP's (string)
 * cast of a bool does NOT produce a valid xsd:boolean lexical form --
 * `(string) true` is "1", not "true". This class was never at risk of
 * that particular mistake (it never relies on an implicit cast either
 * before or after this session's change), but it's the reason explicit
 * literal strings, not casts, are used throughout.
 *
 * CONFIRMED (2026-09-16, carried over, unaffected by this session's
 * change) against the installed Wikibase source: addValue() takes SIX
 * parameters, including the un-typed $dataType and $snakNamespace, which
 * are accepted but unused -- see the previous version of this docblock
 * for the full signature-verification story if needed; nothing about
 * that changed here.
 *
 * Still deliberately unresolved (see CONTEXT.md's open questions):
 * whether to also register a custom 'rdf-type-uri' for PT:boolean, or
 * accept whatever URI Wikibase's RdfVocabulary auto-generates.
 *
 * @license GPL-2.0-or-later
 */
class BooleanRdfMapper implements ValueSnakRdfBuilder {

	/**
	 * Writes the RDF representation of $snak's boolean value, as a plain
	 * xsd:boolean literal -- e.g. `wdt:P123 "true"^^xsd:boolean .` -- with
	 * no auxiliary value node (unlike Quantity/GlobeCoordinate/Time,
	 * boolean has no bounds, unit, precision, or calendar model that
	 * would need one).
	 *
	 * @param RdfWriter $writer
	 * @param string $propertyValueNamespace RDF namespace for the
	 *   property-value relation (e.g. Wikibase's "wdt"-equivalent for a
	 *   standalone install).
	 * @param string $propertyValueLName Local name for the property-value
	 *   relation.
	 * @param string $dataType The property data type, expected to be
	 *   "boolean" here. Unused: a boolean snak's RDF shape doesn't
	 *   depend on the data type string itself, only on the value.
	 * @param string $snakNamespace RDF namespace for this snak's own
	 *   subject, used by other builders (see QuantityRdfBuilder) to
	 *   attach an auxiliary value node. Unused here: boolean has nothing
	 *   to attach.
	 * @param PropertyValueSnak $snak The snak whose DataValues\StringValue
	 *   -- holding exactly BooleanStrings::TRUE or BooleanStrings::FALSE
	 *   in the normal case -- is to be written.
	 */
	public function addValue(
		RdfWriter $writer,
		$propertyValueNamespace,
		$propertyValueLName,
		$dataType,
		$snakNamespace,
		PropertyValueSnak $snak
	) {
		/** @var StringValue $value */
		$value = $snak->getDataValue();
		'@phan-var StringValue $value';

		// Exact string equality, never truthiness -- see this class's
		// docblock for why relying on truthiness here would silently
		// mis-report every "false" value as "true".
		$lexicalValue = $value->getValue() === BooleanStrings::TRUE
			? BooleanStrings::TRUE
			: BooleanStrings::FALSE;

		$writer->say( $propertyValueNamespace, $propertyValueLName )
			->value( $lexicalValue, 'xsd', 'boolean' );
	}

}