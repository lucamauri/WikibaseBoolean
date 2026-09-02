<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Rdf;

use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\Repo\Rdf\ValueSnakRdfBuilder;
use Wikimedia\Purtle\RdfWriter;

/**
 * Maps a boolean-valued snak to RDF for the Wikibase Query Service (and any
 * other RDF export), as xsd:boolean.
 *
 * CRITICAL correctness note for whoever implements addValue() below:
 * PHP's (string) cast of a bool does NOT produce a valid xsd:boolean
 * lexical form -- `(string) true` is "1", not "true". xsd:boolean's valid
 * lexical values are "true"/"false" (or "1"/"0", but pick one convention
 * and use it consistently -- mixing them silently breaks SPARQL value
 * comparisons and reasoners downstream in the Query Service). Do not rely
 * on implicit PHP casting anywhere in this class; map true/false to
 * literal strings explicitly.
 *
 * The exact addValue() signature below is a best-effort reconstruction
 * from Wikibase core's own ValueSnakRdfBuilder implementations (e.g.
 * GlobeCoordinateRdfBuilder). It has NOT been confirmed against the
 * currently-installed Wikibase version's actual interface this session --
 * the parameter list (and possibly the interface's exact namespace) has
 * changed across Wikibase versions in the past, so re-check
 * Wikibase\Repo\Rdf\ValueSnakRdfBuilder in the vendored source before
 * writing real logic here.
 *
 * Also deliberately unresolved by this scaffold (see CONTEXT.md's open
 * questions and the WikibaseBoolean.datatypes.php header comment): whether
 * to also register a custom 'rdf-type-uri' for PT:boolean, or accept
 * whatever URI Wikibase's RdfVocabulary auto-generates.
 *
 * @license GPL-2.0-or-later
 */
class BooleanRdfMapper implements ValueSnakRdfBuilder {

	/**
	 * Writes the RDF representation of $snak's boolean value.
	 *
	 * @param RdfWriter $writer
	 * @param string $propertyValueNamespace RDF namespace for the
	 *   property-value relation (e.g. Wikibase's "wdt"-equivalent for a
	 *   standalone install).
	 * @param string $propertyValueLName Local name for the property-value
	 *   relation.
	 * @param string $dataType The property data type, expected to be
	 *   "boolean" here.
	 * @param PropertyValueSnak $snak The snak whose DataValues\BooleanValue
	 *   is to be written.
	 */
	public function addValue(
		RdfWriter $writer,
		string $propertyValueNamespace,
		string $propertyValueLName,
		string $dataType,
		PropertyValueSnak $snak
	): void {
		throw new \LogicException( 'BooleanRdfMapper::addValue() is not implemented yet.' );
	}

}
