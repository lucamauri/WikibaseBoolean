<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Parsers;

use DataValues\StringValue;
use MediaWiki\Extension\WikibaseBoolean\BooleanStrings;
use ValueParsers\ParseException;
use ValueParsers\ValueParser;

/**
 * Parses raw user input into a DataValues\StringValue holding exactly the
 * literal string BooleanStrings::TRUE or BooleanStrings::FALSE.
 *
 * SUPERSEDED (2026-09-18): this class used to return a native
 * DataValues\BooleanValue -- see
 * manuals/adr/0001-reuse-datavalues-booleanvalue.md, now itself superseded
 * by manuals/adr/0006-boolean-as-string-value-type.md. That change is not
 * a preference; it exists because a real, fully traced bug in Wikibase
 * core (wikibase.api.ParseValueCaller's `result.value && result.type`
 * truthy check on the client side, compounded by MediaWiki's own legacy
 * BC-mode JSON API rendering of raw PHP booleans as "" for true and an
 * absent key for false) meant a BooleanValue could never survive the
 * client-side "is this value valid" round trip for EITHER true or false --
 * the Save button stayed disabled forever, with no thrown error anywhere,
 * the first and only time this was actually tried live. A DataValues\
 * StringValue sidesteps both problems: a non-empty string is always
 * truthy in JS regardless of whether its content is "true" or "false",
 * and it deserializes client-side via dataValues.StringValue, which
 * accepts any string unconditionally -- unlike dataValues.BoolValue's
 * strict `typeof value !== 'boolean'` guard. See the ADR for the full
 * trace, file by file.
 *
 * INPUT ACCEPTANCE is completely unchanged by that switch and still
 * exactly matches PHP's own FILTER_VALIDATE_BOOLEAN semantics
 * (case-insensitive):
 *   - true-ish:  "true", "1", "yes", "on"  (or a native PHP `true`)
 *   - false-ish: "false", "0", "no", "off", "" (or a native PHP `false`)
 * Leading/trailing whitespace on string input is trimmed before matching.
 * Anything else -- including numbers other than 0/1, null, arrays,
 * objects -- is rejected with a ParseException. Only the OUTPUT shape
 * changed; what counts as valid input, and the reasoning for delegating
 * to filter_var() rather than bespoke string matching, is exactly as
 * before.
 *
 * NOT YET VERIFIED this session (carried over, still true): the exact
 * constructor signature of ValueParsers\ParseException. This class
 * currently calls it with a single message-string argument, which is
 * guaranteed to work if the constructor's other parameters are optional,
 * but check vendor/data-values/interfaces/src/ValueParsers/ParseException.php
 * against the actual installed version before assuming either way.
 *
 * @license GPL-2.0-or-later
 */
class BooleanParser implements ValueParser {

	/**
	 * @inheritDoc
	 *
	 * @param mixed $value Raw input to parse. A native bool is accepted
	 *   and converted directly. Any other scalar (string, int, float) is
	 *   matched against the accepted-token list documented on this class,
	 *   via PHP's own FILTER_VALIDATE_BOOLEAN. Non-scalar input (null,
	 *   array, object) is rejected outright.
	 *
	 * @return StringValue Holding exactly BooleanStrings::TRUE or
	 *   BooleanStrings::FALSE -- never any other string.
	 *
	 * @throws ParseException If $value is non-scalar, or is a scalar
	 *   that doesn't match any of the accepted true/false tokens.
	 */
	public function parse( $value ) {
		if ( is_bool( $value ) ) {
			return $this->newStringValue( $value );
		}

		if ( !is_scalar( $value ) ) {
			throw new ParseException(
				'BooleanParser can only parse scalar input (bool, string, int, '
					. 'float); got ' . gettype( $value ) . '.'
			);
		}

		if ( is_string( $value ) ) {
			$value = trim( $value );
		}

		$parsed = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		if ( $parsed === null ) {
			throw new ParseException( sprintf(
				'Cannot parse "%s" as a boolean. Accepted values (case-insensitive): '
					. 'true/false, 1/0, yes/no, on/off.',
				(string)$value
			) );
		}

		return $this->newStringValue( $parsed );
	}

	/**
	 * The one and only place this class constructs its return value, so
	 * BooleanStrings::TRUE/FALSE are guaranteed to be the only two strings
	 * that can ever come out of parse() -- see this class's own docblock
	 * for why that guarantee matters more now than it used to.
	 */
	private function newStringValue( bool $value ): StringValue {
		return new StringValue( $value ? BooleanStrings::TRUE : BooleanStrings::FALSE );
	}

}