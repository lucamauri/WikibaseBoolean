<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Validators;

use DataValues\BooleanValue;
use ValueValidators\Error;
use ValueValidators\Result;
use ValueValidators\ValueValidator;

/**
 * Validates a DataValues\BooleanValue beyond what parsing already
 * guarantees structurally.
 *
 * For most Wikibase data types this is where meaningful constraint
 * checking happens (e.g. string length, quantity bounds, URL scheme
 * whitelists). For a genuine 2-state boolean -- which is this project's
 * entire point, per CONTEXT.md's explicit scope decision to skip
 * somevalue/novalue -- there is very little left to validate once parsing
 * has already produced a real DataValues\BooleanValue: a PHP bool is
 * inherently either true or false, with no further range or format to
 * police. This class is deliberately thin; if it ever grows constraint
 * logic beyond "is this actually a BooleanValue", that's a sign the
 * parser is under-validating instead, not a reason to expand this class.
 *
 * CONFIRMED (2026-09-16) against the installed data-values/interfaces
 * source (vendor/data-values/interfaces/src/ValueValidators/): Result.php,
 * Error.php and ValueValidator.php. Two corrections to this class's
 * earlier stub, both drawn from that source directly rather than from
 * established convention:
 *
 *   - ValueValidators\ValueValidator declares only validate( $value ):
 *     Result. There is no setOptions() in the contract -- the stub's
 *     previous docblock claim that "the method must exist to satisfy the
 *     ValueValidator contract" was incorrect, in the same vein as the
 *     FORMAT_PLAIN/FORMAT_HTML mistake corrected in BooleanFormatter. This
 *     class therefore does not declare setOptions() at all.
 *   - Result::newError() takes Error[], and Error::newError() takes
 *     ( string $text, ?string $property = null, string $code = 'invalid',
 *     array $params = [] ) -- confirmed directly, not reconstructed.
 *
 * @license GPL-2.0-or-later
 */
class BooleanValidator implements ValueValidator {

	/**
	 * The only failure mode this validator actually guards against: being
	 * handed something other than a DataValues\BooleanValue. This should
	 * never happen on a normal Wikibase path -- BooleanParser is the only
	 * thing expected to feed this validator, and it only ever produces a
	 * BooleanValue or throws -- so this is a defensive check against a
	 * mismatched registration elsewhere in the datatype definition, not
	 * an expected runtime outcome. See this class's own docblock for why
	 * there is nothing else left to validate once that check passes: a
	 * PHP bool has no range, length, or format to be invalid in.
	 */
	private const ERROR_CODE_NOT_A_BOOLEAN_VALUE = 'wikibaseboolean-not-a-boolean-value';

	/**
	 * @inheritDoc
	 *
	 * @param mixed $value Expected to be a BooleanValue; anything else
	 *   fails validation (see ERROR_CODE_NOT_A_BOOLEAN_VALUE's docblock).
	 *
	 * @return Result A success if $value is a BooleanValue, otherwise a
	 *   Result carrying a single descriptive Error. Note this method
	 *   never throws for a bad $value -- returning a failed Result is
	 *   ValueValidator's actual contract, unlike ValueFormatter/ValueParser
	 *   elsewhere in this extension, which signal failure via exceptions.
	 */
	public function validate( $value ): Result {
		if ( !( $value instanceof BooleanValue ) ) {
			return Result::newError( [
				Error::newError(
					'Expected a DataValues\BooleanValue, got '
						. ( is_object( $value ) ? get_class( $value ) : gettype( $value ) ) . '.',
					null,
					self::ERROR_CODE_NOT_A_BOOLEAN_VALUE
				),
			] );
		}

		return Result::newSuccess();
	}

}