<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Validators;

use MediaWiki\Extension\WikibaseBoolean\Validators\BooleanValidator;
use PHPUnit\Framework\TestCase;
use ValueValidators\ValueValidator;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Validators\BooleanValidator
 *
 * Skeleton only -- see BooleanParserTest's docblock for the rationale.
 *
 * @license GPL-2.0-or-later
 */
class BooleanValidatorTest extends TestCase {

	public function testImplementsValueValidator(): void {
		$validator = new BooleanValidator();

		$this->assertInstanceOf( ValueValidator::class, $validator );
	}

}
