<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Validators;

use DataValues\BooleanValue;
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
 * police. This class is expected to stay thin; if it grows constraint
 * logic beyond "is this actually a BooleanValue", that's worth revisiting
 * as a sign the parser is under-validating instead.
 *
 * The exact ValueValidator method signatures below (validate/setOptions)
 * are written from established convention in the data-values/validators
 * ecosystem, but have NOT been re-verified against the actual installed
 * package version this session -- confirm against the real vendored
 * interface before implementing real logic.
 *
 * @license GPL-2.0-or-later
 */
class BooleanValidator implements ValueValidator {

	/**
	 * @inheritDoc
	 *
	 * @param BooleanValue $value
	 *
	 * @return Result A successful Result, or one describing why $value
	 *   is invalid. Not implemented yet.
	 */
	public function validate( $value ) {
		throw new \LogicException( 'BooleanValidator::validate() is not implemented yet.' );
	}

	/**
	 * @inheritDoc
	 *
	 * No options are anticipated for a 2-state boolean validator, but the
	 * method must exist to satisfy the ValueValidator contract.
	 *
	 * @param array<string, mixed> $options
	 */
	public function setOptions( array $options ) {
		// Intentionally empty: no configurable options yet.
	}

}
