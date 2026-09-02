<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Parsers;

use DataValues\BooleanValue;
use ValueParsers\ParseException;
use ValueParsers\ValueParser;

/**
 * Parses raw user input into a DataValues\BooleanValue.
 *
 * ACCEPTED INPUT (case-insensitive, matching PHP's own
 * FILTER_VALIDATE_BOOLEAN semantics, which this class delegates to
 * rather than reinventing string-matching rules):
 *   - true-ish:  "true", "1", "yes", "on"  (or a native PHP `true`)
 *   - false-ish: "false", "0", "no", "off", "" (or a native PHP `false`)
 * Leading/trailing whitespace on string input is trimmed before
 * matching. Anything else -- including numbers other than 0/1, null,
 * arrays, objects -- is rejected with a ParseException.
 *
 * WHY delegate to filter_var() instead of a bespoke set of string
 * comparisons: FILTER_VALIDATE_BOOLEAN is a stable, well-known PHP
 * standard-library behavior (not a WikibaseBoolean invention), so the
 * accepted-token list above is documentation of *its* behavior, not an
 * arbitrary choice made here. This also means WikibaseBoolean doesn't
 * carry its own copy of "which strings mean true" logic to maintain.
 *
 * WHY native bool is special-cased at the top rather than relying on
 * filter_var()'s implicit (string) cast of it: `(string) true` is "1"
 * (matches) and `(string) false` is "" (also a recognized false token,
 * per FILTER_VALIDATE_BOOLEAN's own documentation) -- so relying on the
 * cast would technically work, but doing so is a subtle, easy-to-break
 * implementation detail to depend on silently. Handling `is_bool()`
 * explicitly up front is clearer to a future reader and one line
 * cheaper besides.
 *
 * NOT YET VERIFIED this session (flagging per this project's own
 * verification discipline -- see manuals/adr/): the exact constructor
 * signature of ValueParsers\ParseException. This class currently calls
 * it with a single message-string argument, which is guaranteed to work
 * if the constructor's other parameters are optional, but a richer
 * ParseException (passing $rawValue / $expectedFormat, if the installed
 * version's constructor supports them) would give calling code -- e.g.
 * the API's error reporting -- more to work with. Check
 * vendor/data-values/interfaces/src/ValueParsers/ParseException.php
 * against the actual installed version before assuming either way.
 *
 * @license GPL-2.0-or-later
 */
class BooleanParser implements ValueParser {

	/**
	 * @inheritDoc
	 *
	 * @param mixed $value Raw input to parse. A native bool is accepted
	 *   and passed straight through. Any other scalar (string, int,
	 *   float) is matched against the accepted-token list documented on
	 *   this class, via PHP's own FILTER_VALIDATE_BOOLEAN. Non-scalar
	 *   input (null, array, object) is rejected outright.
	 *
	 * @return BooleanValue
	 *
	 * @throws ParseException If $value is non-scalar, or is a scalar
	 *   that doesn't match any of the accepted true/false tokens.
	 */
	public function parse( $value ) {
		if ( is_bool( $value ) ) {
			return new BooleanValue( $value );
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

		return new BooleanValue( $parsed );
	}

}
