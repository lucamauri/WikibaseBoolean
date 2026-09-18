<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean;

/**
 * The exactly two string values WikibaseBoolean's underlying
 * DataValues\StringValue is ever allowed to hold.
 *
 * INTRODUCED (2026-09-18) alongside
 * manuals/adr/0006-boolean-as-string-value-type.md. Before that ADR,
 * "is this actually a valid 2-state boolean" was guaranteed for free by
 * the type system: a native PHP bool has no other possible state. Now
 * that the underlying value is a plain string -- chosen specifically to
 * survive a real bug in Wikibase core's own client-side value-parsing
 * round trip, see the ADR for the full trace -- that guarantee is gone.
 * BooleanParser, BooleanValidator, BooleanFormatter, and BooleanRdfMapper
 * all need to agree on exactly what "valid" means for that string. These
 * two constants are that single source of truth, so the literal strings
 * "true"/"false" never need to be typed out -- and risk drifting out of
 * sync with each other -- in more than one place.
 *
 * Deliberately a plain final class of constants, not an enum: this
 * extension's minimum PHP version (composer.json: >=8.1) does support
 * native enums, but a `BooleanValue` case wrapping a bool would bring
 * back exactly the native-boolean shape this whole change exists to get
 * away from, and a plain string-backed enum adds ceremony (::value
 * everywhere) for no benefit over two string constants here.
 *
 * @license GPL-2.0-or-later
 */
final class BooleanStrings {

	public const TRUE = 'true';
	public const FALSE = 'false';

	private function __construct() {
		// Not meant to be instantiated -- constants only.
	}

}