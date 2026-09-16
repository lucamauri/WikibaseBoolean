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
 * RESOLVED -- deserialization DOES need an explicit registration:
 * confirmed against `vendor/data-values/data-values/src/BooleanValue.php`
 * on a real Wikibase install (its deprecated `newFromArray()` docblock
 * points at "DataValue builder callbacks in {@see DataValueDeserializer}"
 * as the non-deprecated replacement), and against Wikibase's own
 * ADR-0024 ("Data type specific value deserialization"), which added a
 * `'deserializer-builder'` field to this exact definitions array for
 * this exact purpose (originally for the entity-schema data type, whose
 * shipped hook handler registers it as a plain class-string:
 * `'deserializer-builder' => EntitySchemaValue::class`). We follow that
 * precedent below -- a stored {"type":"boolean","value":true} blob would
 * NOT deserialize back into a `DataValues\BooleanValue` without this,
 * since Wikibase core has never registered a 'boolean' value type (see
 * the VT:boolean note above) and so has no default deserializer for it.
 *
 * @note This is bootstrap code, executed on every request. Avoid
 * instantiating heavy objects here -- only return callbacks.
 *
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanFormatter;
use MediaWiki\Extension\WikibaseBoolean\Formatters\MediaWikiBooleanMessageLookup;
use MediaWiki\Extension\WikibaseBoolean\Parsers\BooleanParser;
use MediaWiki\Extension\WikibaseBoolean\Rdf\BooleanRdfMapper;
use MediaWiki\Extension\WikibaseBoolean\Validators\BooleanValidator;

return [

	'PT:boolean' => [

		// The underlying DataValues shape. See the architecture note above:
		// this points at the upstream DataValues\BooleanValue, not a
		// WikibaseBoolean-owned class.
		'value-type' => 'boolean',

		// Tells Wikibase's DataValueDeserializer how to turn a stored
		// {"type":"boolean","value":true} blob back into a real
		// DataValues\BooleanValue on load -- see the RESOLVED note above.
		// A plain class-string is sufficient (mirroring EntitySchema's own
		// registration) because BooleanValue's constructor accepts exactly
		// the raw value the deserializer already has in hand (a native
		// bool, once json_decode has done its job).
		'deserializer-builder' => \DataValues\BooleanValue::class,

		// Factory for the parser that turns raw user input (e.g. a checkbox
		// state, or typed text like "true"/"yes") into a
		// DataValues\BooleanValue. Signature/parameters intentionally left
		// to be confirmed against the installed ValueParsers factory
		// contract when real logic is implemented.
		'parser-factory-callback' => static function () {
			return new BooleanParser();
		},

		// Factory for the formatter that turns a DataValues\BooleanValue
		// into a display representation (plain text, HTML with a glyph
		// prefix, or wikitext -- see BooleanFormatter's own docblock for
		// exactly which format gets which treatment).
		//
		// MediaWikiBooleanMessageLookup is constructed here, at the one
		// point where WikibaseBoolean is definitely running inside a real
		// MediaWiki request, and handed to BooleanFormatter rather than
		// letting BooleanFormatter call wfMessage() itself. This is what
		// keeps BooleanFormatter (and BooleanFormatterTest) testable with
		// plain PHPUnit -- see BooleanMessageLookup's own docblock for the
		// full rationale.
		'formatter-factory-callback' => static function ( $format ) {
			return new BooleanFormatter( $format, new MediaWikiBooleanMessageLookup() );
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