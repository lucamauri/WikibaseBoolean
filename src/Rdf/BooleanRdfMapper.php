<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Rdf;

use DataValues\BooleanValue;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\Repo\Rdf\ValueSnakRdfBuilder;
use Wikimedia\Purtle\RdfWriter;

/**
 * Maps a boolean-valued snak to RDF for the Wikibase Query Service (and any
 * other RDF export), as xsd:boolean.
 *
 * CONFIRMED (2026-09-16) against the installed Wikibase source
 * (extensions/Wikibase/repo/includes/Rdf/ValueSnakRdfBuilder.php and two
 * real implementations, MonolingualTextRdfBuilder and QuantityRdfBuilder,
 * both under .../Rdf/Values/). Two corrections to this class's earlier
 * scaffold, both real bugs that would have been fatal signature mismatches
 * or silently-wrong RDF, not style choices:
 *
 *   - addValue() takes SIX parameters, not five. The scaffold was missing
 *     $snakNamespace, which sits between $dataType and $snak. Getting this
 *     wrong wouldn't have been a subtle bug -- PHP would fatal on
 *     "Declaration must be compatible with
 *     ValueSnakRdfBuilder::addValue()" the moment Wikibase tried to use
 *     this class.
 *   - The interface itself declares no type hints on the string
 *     parameters (or a return type). Both real implementations checked
 *     leave them untyped too, rather than adding stricter hints -- adding
 *     e.g. `string $dataType` here would actually be a fatal LSP
 *     violation (narrowing an implicitly-mixed parameter), not just an
 *     inconsistency, so this class deliberately matches the interface's
 *     own looseness on those four parameters instead of over-typing them.
 *
 * $dataType and $snakNamespace are accepted but unused below -- the same
 * pattern MonolingualTextRdfBuilder uses for a single fixed-shape literal
 * value with no unit/normalization/complex-value node of its own. Compare
 * QuantityRdfBuilder, which *does* use $snakNamespace, but only because it
 * writes an auxiliary "value node" for bounds/unit/normalization -- boolean
 * has nothing equivalent to attach.
 *
 * CRITICAL correctness note, carried over from the original scaffold and
 * still the reason this class exists: PHP's (string) cast of a bool does
 * NOT produce a valid xsd:boolean lexical form -- `(string) true` is "1",
 * not "true". xsd:boolean's valid lexical values are "true"/"false" (or
 * "1"/"0", but mixing the two conventions silently breaks SPARQL value
 * comparisons downstream in the Query Service). This class maps true/false
 * to the literal strings 'true'/'false' explicitly, via a ternary, never
 * an implicit cast.
 *
 * Still deliberately unresolved (see CONTEXT.md's open questions and the
 * WikibaseBoolean.datatypes.php header comment): whether to also register
 * a custom 'rdf-type-uri' for PT:boolean, or accept whatever URI
 * Wikibase's RdfVocabulary auto-generates. Nothing below touches that --
 * it's a separate registration concern, not part of addValue() itself.
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
	 * @param PropertyValueSnak $snak The snak whose DataValues\BooleanValue
	 *   is to be written.
	 */
	public function addValue(
		RdfWriter $writer,
		$propertyValueNamespace,
		$propertyValueLName,
		$dataType,
		$snakNamespace,
		PropertyValueSnak $snak
	) {
		/** @var BooleanValue $value */
		$value = $snak->getDataValue();
		'@phan-var BooleanValue $value';

		// Explicit literal strings, never an implicit (string) cast --
		// see this class's docblock for why that distinction matters for
		// xsd:boolean specifically.
		$lexicalValue = $value->getValue() ? 'true' : 'false';

		$writer->say( $propertyValueNamespace, $propertyValueLName )
			->value( $lexicalValue, 'xsd', 'boolean' );
	}

}