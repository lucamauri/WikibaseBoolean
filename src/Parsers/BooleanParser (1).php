<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Parsers;

use DataValues\BooleanValue;
use ValueParsers\ParseException;
use ValueParsers\ValueParser;

/**
 * Parses raw user input into a DataValues\BooleanValue.
 *
 * "Raw user input" here means whatever a ValueParser in the Wikibase/
 * DataValues ecosystem is expected to accept: typically a string, since
 * parsers sit between free-text entry (or a non-JS fallback) and a
 * structured DataValue. If the UI widget (see CONTEXT.md's "Technical
 * shape" -- a checkbox/toggle, not yet scaffolded) always sends a real
 * boolean rather than a string, this parser may end up doing very little
 * work; it still needs to exist so Wikibase's parser-factory-callback
 * contract is satisfied and so non-JS / API-driven input has a defined,
 * lenient entry point (e.g. accepting "true"/"false", "1"/"0", "yes"/"no").
 *
 * Deliberately NOT implemented yet -- this class exists to fix the shape
 * of the parsing layer for architecture review before real parsing rules
 * (which strings count as true/false, whether parsing is case-insensitive,
 * what locale-specific variants if any) are decided and written.
 *
 * @license GPL-2.0-or-later
 */
class BooleanParser implements ValueParser {

	/**
	 * @inheritDoc
	 *
	 * @param mixed $value Raw input to parse. Expected shape (string vs.
	 *   native bool vs. something else) is intentionally undecided here --
	 *   confirm against how the eventual UI widget and the Wikibase API
	 *   actually submit values before implementing.
	 *
	 * @return BooleanValue
	 *
	 * @throws ParseException If $value cannot be parsed as a boolean.
	 */
	public function parse( $value ) {
		throw new ParseException( 'BooleanParser::parse() is not implemented yet.' );
	}

}
