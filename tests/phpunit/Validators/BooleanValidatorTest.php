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
 * Only depends on data-values/interfaces (ValueValidators\Result/Error),
 * confirmed present and correctly located there -- not in
 * data-values/validators, despite composer.json currently requiring that
 * package too (see BooleanValidator's own docblock; worth a follow-up ADR
 * on whether that composer.json dependency is actually needed). Runs
 * under plain `composer test`, same as BooleanParserTest and
 * BooleanFormatterTest.
 *
 * @license GPL-2.0-or-later
 */
class BooleanValidatorTest extends TestCase {

	public function testImplementsValueValidator(): void {
		$this->assertInstanceOf( ValueValidator::class, new BooleanValidator() );
	}

	/**
	 * @dataProvider booleanValueProvider
	 */
	public function testAcceptsAnyBooleanValue( bool $rawValue ): void {
		$result = ( new BooleanValidator() )->validate( new BooleanValue( $rawValue ) );

		$this->assertInstanceOf( Result::class, $result );
		$this->assertTrue( $result->isValid() );
		$this->assertSame( [], $result->getErrors() );
	}

	public static function booleanValueProvider(): array {
		return [
			'true' => [ true ],
			'false' => [ false ],
		];
	}

	/**
	 * @dataProvider nonBooleanValueProvider
	 *
	 * BooleanParser is the only thing expected to feed this validator in
	 * practice, and it never produces anything but a BooleanValue -- so
	 * this covers the defensive path (a mismatched registration), not an
	 * expected runtime one. See BooleanValidator's own docblock.
	 */
	public function testRejectsAnythingThatIsNotABooleanValue( $notABooleanValue ): void {
		$result = ( new BooleanValidator() )->validate( $notABooleanValue );

		$this->assertFalse( $result->isValid() );
		$this->assertCount( 1, $result->getErrors() );
	}

	public static function nonBooleanValueProvider(): array {
		return [
			'native bool (not wrapped in BooleanValue)' => [ true ],
			'string' => [ 'true' ],
			'null' => [ null ],
			'a different DataValue' => [ new StringValue( 'not boolean' ) ],
		];
	}

}