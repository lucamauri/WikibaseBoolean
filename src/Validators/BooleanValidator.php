<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Validators;

use DataValues\StringValue;
use MediaWiki\Extension\WikibaseBoolean\BooleanStrings;
use ValueValidators\Error;
use ValueValidators\Result;
use ValueValidators\ValueValidator;

/**
 * Validates a DataValues\StringValue as WikibaseBoolean actually uses it:
 * exactly the literal string BooleanStrings::TRUE or BooleanStrings::FALSE,
 * nothing else.
 *
 * SUPERSEDED (2026-09-18): this class's entire original rationale was
 * that there was very little to validate, because a native PHP bool is
 * inherently 2-state -- see
 * manuals/adr/0001-reuse-datavalues-booleanvalue.md (now itself
 * superseded by manuals/adr/0006-boolean-as-string-value-type.md) and
 * this class's own prior docblock. That reasoning held exactly as long as
 * the underlying value really was a DataValues\BooleanValue. It no longer
 * is: WikibaseBoolean now deliberately stores its value as a plain
 * DataValues\StringValue instead, specifically to survive a real,
 * fully-traced bug in Wikibase core's client-side value-parsing round
 * trip (see the ADR). A string is NOT inherently 2-state -- it can be
 * anything -- so the guarantee this class used to get for free from the
 * type system is gone, and checking "is this actually 'true' or 'false',
 * and nothing else" is now this class's real job, not a formality.
 *
 * BooleanParser is still the only thing expected to feed this validator on
 * a normal Wikibase path, and it only ever produces exactly
 * BooleanStrings::TRUE or BooleanStrings::FALSE (or throws a
 * ParseException, never reaching validation at all). So in practice this
 * should never actually reject anything reachable through normal use --
 * but unlike the previous BooleanValue-based version, that is now true
 * because of what BooleanParser promises to do, not because of what the
 * type system enforces. This validator is what makes that promise a
 * checked one, for any StringValue that reaches it some other way (a
 * malformed edit via the raw API, hand-edited page content, a future bug
 * in BooleanParser).
 *
 * CONFIRMED (2026-09-16, carried over from the prior version of this
 * class) against the installed data-values/interfaces source
 * (vendor/data-values/interfaces/src/ValueValidators/): ValueValidators\
 * ValueValidator declares only validate( $value ): Result, no
 * setOptions() -- unaffected by this session's change, still accurate.
 *
 * @license GPL-2.0-or-later
 */
class BooleanValidator implements ValueValidator {

	private const ERROR_CODE_NOT_A_STRING_VALUE = 'wikibaseboolean-not-a-string-value';
	private const ERROR_CODE_NOT_TRUE_OR_FALSE = 'wikibaseboolean-not-true-or-false';

	/**
	 * @inheritDoc
	 *
	 * @param mixed $value Expected to be a StringValue holding exactly
	 *   BooleanStrings::TRUE or BooleanStrings::FALSE; anything else fails
	 *   validation.
	 *
	 * @return Result A success only if $value is a StringValue holding
	 *   exactly one of the two accepted strings; otherwise a Result
	 *   carrying a single descriptive Error explaining which check failed.
	 *   As with the previous version of this class, this never throws for
	 *   a bad $value -- returning a failed Result is ValueValidator's
	 *   actual contract.
	 */
	public function validate( $value ): Result {
		if ( !( $value instanceof StringValue ) ) {
			return Result::newError( [
				Error::newError(
					'Expected a DataValues\StringValue, got '
						. ( is_object( $value ) ? get_class( $value ) : gettype( $value ) ) . '.',
					null,
					self::ERROR_CODE_NOT_A_STRING_VALUE
				),
			] );
		}

		$rawValue = $value->getValue();

		if ( $rawValue !== BooleanStrings::TRUE && $rawValue !== BooleanStrings::FALSE ) {
			return Result::newError( [
				Error::newError(
					'Expected exactly "' . BooleanStrings::TRUE . '" or "'
						. BooleanStrings::FALSE . '", got "' . $rawValue . '".',
					null,
					self::ERROR_CODE_NOT_TRUE_OR_FALSE
				),
			] );
		}

		return Result::newSuccess();
	}

}