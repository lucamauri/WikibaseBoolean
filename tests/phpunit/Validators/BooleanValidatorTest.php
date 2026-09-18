<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Validators;

use DataValues\BooleanValue;
use DataValues\StringValue;
use MediaWiki\Extension\WikibaseBoolean\Validators\BooleanValidator;
use PHPUnit\Framework\TestCase;
use ValueValidators\Result;
use ValueValidators\ValueValidator;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Validators\BooleanValidator
 *
 * SUPERSEDED (2026-09-18): this suite used to confirm that BooleanValidator
 * accepted ANY BooleanValue and rejected anything that wasn't one -- a
 * type-only check, since a native bool had no other state to be wrong in.
 * See BooleanValidator's own docblock and
 * manuals/adr/0006-boolean-as-string-value-type.md for why that's no
 * longer sufficient: the underlying value is a plain StringValue now, so
 * this suite exercises real content validation (exactly "true"/"false",
 * nothing else) rather than just a type check. Notably, a StringValue
 * holding "1" -- something BooleanParser itself would normalize to the
 * string "true" before it ever reaches this validator -- is deliberately
 * tested as REJECTED here: the whole point of centralizing on exactly two
 * canonical strings is that nothing downstream has to guess at
 * BooleanParser's own input-normalization behaviour.
 *
 * Only depends on data-values/interfaces (ValueValidators\Result/Error).
 * Runs under plain `composer test`, same as BooleanParserTest and
 * BooleanFormatterTest.
 *
 * @license GPL-2.0-or-later
 */
class BooleanValidatorTest extends TestCase {

	public function testImplementsValueValidator(): void {
		$this->assertInstanceOf( ValueValidator::class, new BooleanValidator() );
	}

	/**
	 * @dataProvider validStringValueProvider
	 */
	public function testAcceptsExactlyTrueOrFalse( string $rawValue ): void {
		$result = ( new BooleanValidator() )->validate( new StringValue( $rawValue ) );

		$this->assertInstanceOf( Result::class, $result );
		$this->assertTrue( $result->isValid() );
		$this->assertSame( [], $result->getErrors() );
	}

	public static function validStringValueProvider(): array {
		return [
			'true' => [ 'true' ],
			'false' => [ 'false' ],
		];
	}

	/**
	 * @dataProvider invalidValueProvider
	 *
	 * BooleanParser is the only thing expected to feed this validator in
	 * practice, and it never produces anything but a StringValue holding
	 * exactly "true" or "false" -- so this covers the defensive path (a
	 * mismatched registration, a malformed edit via the raw API, or a
	 * future bug in BooleanParser), not an expected runtime one. See
	 * BooleanValidator's own docblock.
	 */
	public function testRejectsAnythingThatIsNotExactlyTrueOrFalse( $invalidValue ): void {
		$result = ( new BooleanValidator() )->validate( $invalidValue );

		$this->assertFalse( $result->isValid() );
		$this->assertCount( 1, $result->getErrors() );
	}

	public static function invalidValueProvider(): array {
		return [
			'native bool (not wrapped in a DataValue at all)' => [ true ],
			'raw string (not wrapped in a StringValue)' => [ 'true' ],
			'null' => [ null ],
			'the old, superseded BooleanValue shape' => [ new BooleanValue( true ) ],
			'StringValue holding an unrelated string' => [ new StringValue( 'not boolean' ) ],
			'StringValue holding "True" (wrong case)' => [ new StringValue( 'True' ) ],
			'StringValue holding "1" (a token BooleanParser accepts as INPUT, but never as its OUTPUT)' =>
				[ new StringValue( '1' ) ],
			'StringValue holding the empty string' => [ new StringValue( '' ) ],
		];
	}

}