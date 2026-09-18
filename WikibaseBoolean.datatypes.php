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
 * SUPERSEDED (2026-09-18) -- 'value-type' is 'string', not 'boolean':
 * this file used to declare 'value-type' => 'boolean' and its own
 * 'deserializer-builder' pointing at the upstream DataValues\BooleanValue.
 * See manuals/adr/0006-boolean-as-string-value-type.md for the full,
 * traced reason: a real bug in Wikibase core's client-side value-parsing
 * round trip (wikibase.api.ParseValueCaller's truthy check on the API
 * response, colliding with MediaWiki's own legacy JSON rendering of raw
 * PHP booleans) meant a genuine 'boolean'-value-typed snak could never
 * actually be saved through the UI -- Save stayed disabled forever, for
 * both true and false, the first and only time this was tried live.
 * Declaring 'value-type' => 'string' instead means every callback below
 * now deals in `DataValues\StringValue` instances holding exactly the
 * literal string "true" or "false" (see BooleanStrings, src/BooleanStrings.php),
 * which survives that exact round trip because a non-empty string is
 * always truthy in JS regardless of its content.
 *
 * ARCHITECTURE NOTE -- why there is no explicit 'deserializer-builder'
 * entry here (this is new: the previous version of this file DID declare
 * one, pointing at DataValues\BooleanValue::class):
 * Wikibase's own lib/WikibaseLib.datatypes.php / repo/WikibaseRepo.datatypes.php
 * already register 'VT:string' => ['deserializer-builder' => StringValue::class, ...]
 * -- confirmed directly against the installed Wikibase source. Because
 * this file's 'PT:boolean' entry now declares 'value-type' => 'string'
 * and does NOT declare its own 'deserializer-builder', Wikibase's own
 * DataTypeDefinitions::resolveValueTypeFallback() (confirmed against
 * lib/includes/DataTypeDefinitions.php) automatically falls back to that
 * existing VT:string registration -- the exact same PT-falls-back-to-VT
 * mechanism ADR-0002 already relies on for other fields, just applying
 * to 'deserializer-builder' too, confirmed to apply uniformly rather than
 * assumed. A stored {"type":"string","value":"true"} blob therefore
 * deserializes back into a real DataValues\StringValue with zero
 * registration of our own -- one fewer thing for WikibaseBoolean to own
 * and keep correct.
 *
 * ARCHITECTURE NOTE -- why there is still no 'VT:boolean' entry, and why
 * that's now a different question than it used to be (see ADR-0002,
 * unaffected in its own reasoning but worth restating given the value-type
 * changed): ADR-0002 explains why WikibaseBoolean never registered its
 * own 'VT:boolean' -- Wikibase core registered no 'boolean' value type to
 * share a fallback with, and WikibaseBoolean was the sole owner of
 * everything boolean-related. That's still true, but now moot for a
 * different reason: this file no longer uses 'boolean' as a value-type
 * at all, for anything. There is nothing to define a VT:boolean fallback
 * FOR any more, on any field.
 *
 * ARCHITECTURE NOTE -- 'expert-module' and the checkbox widget (unaffected
 * by this session's value-type change, still accurate): 'expert-module'
 * points at a ResourceLoader module name defining a
 * `jQuery.valueview.Expert`; confirmed as the real, current mechanism
 * against Wikibase's own repo/WikibaseRepo.datatypes.php and the
 * installed WikibaseBoolean. The module name itself,
 * 'jquery.valueview.experts.Boolean', is deliberately NOT
 * extension-prefixed -- see manuals/adr/0005-expert-module-naming.md.
 * See resources/experts/Boolean.js for the Expert implementation, updated
 * this session to read the new StringValue-shaped value correctly.
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

		// See the "SUPERSEDED" architecture note above: this used to be
		// 'boolean', pointing at the upstream DataValues\BooleanValue.
		// It's 'string' now -- every callback below deals in
		// DataValues\StringValue instances holding exactly
		// BooleanStrings::TRUE or BooleanStrings::FALSE (see
		// src/BooleanStrings.php) -- specifically so a stored value
		// survives Wikibase core's client-side parsing round trip. This
		// is a correctness fix for a real, traced bug, not a style
		// preference; see manuals/adr/0006-boolean-as-string-value-type.md.
		'value-type' => 'string',

		// No 'deserializer-builder' here deliberately -- see the
		// architecture note above: falls back to Wikibase's own existing
		// VT:string registration automatically, confirmed against real
		// source rather than assumed.

		// The ResourceLoader module providing the checkbox edit widget.
		// See the 'expert-module' architecture note above and
		// resources/experts/Boolean.js.
		'expert-module' => 'jquery.valueview.experts.Boolean',

		// Factory for the parser that turns raw user input (e.g. the
		// checkbox widget's 'true'/'false' string, or typed text like
		// "yes"/"on") into a DataValues\StringValue holding exactly
		// BooleanStrings::TRUE or BooleanStrings::FALSE.
		'parser-factory-callback' => static function () {
			return new BooleanParser();
		},

		// Factory for the formatter that turns that DataValues\StringValue
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
		// full rationale. Unaffected by this session's value-type change.
		'formatter-factory-callback' => static function ( $format ) {
			return new BooleanFormatter( $format, new MediaWikiBooleanMessageLookup() );
		},

		// Factory for constraint validation. Unlike before this session,
		// this is NOT a thin, near-formality check any more -- a string
		// isn't inherently 2-state the way a native bool was, so this is
		// the one place that actually enforces "exactly 'true' or
		// 'false', nothing else". See BooleanValidator's own docblock.
		//
		// MUST return an array, even though there is only one validator --
		// confirmed (2026-09-18) against
		// repo/includes/BuilderBasedDataTypeValidatorFactory.php::getValidators(),
		// which asserts is_array() on this callback's return value and
		// throws "Factory function for boolean did not return an array of
		// ValueValidator objects" otherwise. This is a genuinely different
		// contract from parser-factory-callback/formatter-factory-callback/
		// rdf-builder-factory-callback just below, which all correctly
		// return a single object: validators are the one callback Wikibase
		// lets a data type chain more than one of (e.g. a generic length
		// check alongside a domain-specific one), so the factory always
		// deals in ValueValidator[], never a bare ValueValidator. This bug
		// predates this session's other changes -- it was simply never
		// reachable before, since Save was never enabled long enough to
		// reach an actual save attempt.
		'validator-factory-callback' => static function () {
			return [ new BooleanValidator() ];
		},

		// Factory for the RDF/Query Service mapping. See BooleanRdfMapper's
		// docblock for the xsd:boolean lexical-form gotcha this must
		// handle correctly (PHP's (string) cast of a bool does NOT produce
		// a valid xsd:boolean literal), AND for a second, new-this-session
		// gotcha specific to the string-typed value: PHP string
		// truthiness would silently mis-report "false" as "true" if this
		// class relied on it, so it doesn't.
		'rdf-builder-factory-callback' => static function () {
			return new BooleanRdfMapper();
		},

	],

];