<?php
/**
 * Wikibase data type definition for the WikibaseBoolean extension.
 *
 * This file follows the same shape as Wikibase core's own bootstrap files
 * (e.g. lib/WikibaseLib.datatypes.php): it returns a plain associative array
 * mapping data type definition keys to factory callbacks, and is merged into
 * Wikibase's data type registry -- in our case via the WikibaseRepoDataTypes
 * hook, handled by \MediaWiki\Extension\WikibaseBoolean\Hooks.
 *
 * ARCHITECTURE NOTE -- why there is no 'VT:boolean' entry here:
 * Wikibase's data type registry distinguishes value types (VT:*, the raw
 * shape of a value) from property data types (PT:*, how a specific property
 * kind interprets that shape). The VT layer only earns its keep when more
 * than one PT shares a value type and can fall back to one shared
 * definition (e.g. PT:url and PT:string both fall back to VT:string).
 * As of this writing, Wikibase core registers no 'boolean' value type at
 * all (checked against lib/WikibaseLib.datatypes.php, which lists only
 * string, globecoordinate, monolingualtext, quantity, time and
 * wikibase-entityid) -- so there is nothing to share, and WikibaseBoolean
 * is deliberately the sole owner of everything boolean-related, declared
 * directly on 'PT:boolean'. If a second boolean-flavoured property data
 * type is ever needed, this can be split into a 'VT:boolean' + thin
 * 'PT:boolean' pair without touching the callbacks themselves.
 *
 * ARCHITECTURE NOTE -- why there is no custom DataValue class:
 * The 'data-values/data-values' package (a hard dependency of Wikibase
 * itself) already ships a trivial `DataValues\BooleanValue` implementation,
 * present since that package's very first release. WikibaseBoolean reuses
 * it directly rather than wrapping it, so 'value-type' below is 'boolean'
 * and every callback deals in `DataValues\BooleanValue` instances.
 *
 * OPEN QUESTION for implementation time (deliberately not resolved by this
 * scaffold): whether Wikibase's DataValueFactory / DataValueDeserializer
 * needs an explicit registration to turn a stored
 * {"type":"boolean","value":true} blob back into a `DataValues\BooleanValue`
 * on load, or whether that is derived automatically from 'value-type'
 * strings already present in the merged registry. Check this against the
 * actual vendored DataValueFactory source before implementing real logic --
 * don't assume either way.
 *
 * @note This is bootstrap code, executed on every request. Avoid
 * instantiating heavy objects here -- only return callbacks.
 *
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanFormatter;
use MediaWiki\Extension\WikibaseBoolean\Parsers\BooleanParser;
use MediaWiki\Extension\WikibaseBoolean\Rdf\BooleanRdfMapper;
use MediaWiki\Extension\WikibaseBoolean\Validators\BooleanValidator;

return [

	'PT:boolean' => [

		// The underlying DataValues shape. See the architecture note above:
		// this points at the upstream DataValues\BooleanValue, not a
		// WikibaseBoolean-owned class.
		'value-type' => 'boolean',

		// Factory for the parser that turns raw user input (e.g. a checkbox
		// state, or typed text like "true"/"yes") into a
		// DataValues\BooleanValue. Signature/parameters intentionally left
		// to be confirmed against the installed ValueParsers factory
		// contract when real logic is implemented.
		'parser-factory-callback' => static function () {
			return new BooleanParser();
		},

		// Factory for the formatter that turns a DataValues\BooleanValue
		// into a display representation (plain, HTML, wikitext -- see
		// BooleanFormatter's docblock for the format-switching contract
		// this still needs to implement).
		'formatter-factory-callback' => static function ( $format ) {
			return new BooleanFormatter( $format );
		},

		// Factory for constraint validation beyond what the parser already
		// guarantees structurally. For a genuine 2-state boolean this is
		// expected to be a thin validator -- see BooleanValidator's
		// docblock for why.
		'validator-factory-callback' => static function () {
			return new BooleanValidator();
		},

		// Factory for the RDF/Query Service mapping. See BooleanRdfMapper's
		// docblock for the xsd:boolean lexical-form gotcha this must
		// handle correctly (PHP's (string) cast of a bool does NOT produce
		// a valid xsd:boolean literal).
		'rdf-builder-factory-callback' => static function () {
			return new BooleanRdfMapper();
		},

	],

];
