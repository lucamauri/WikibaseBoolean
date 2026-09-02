<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Parsers;

use DataValues\BooleanValue;
use MediaWiki\Extension\WikibaseBoolean\Parsers\BooleanParser;
use PHPUnit\Framework\TestCase;
use ValueParsers\ParseException;
use ValueParsers\ValueParser;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Parsers\BooleanParser
 *
 * Only depends on data-values/data-values and data-values/interfaces --
 * both standalone Composer packages -- so unlike BooleanRdfMapperTest and
 * HooksTest, this suite is expected to run in a plain `composer test`
 * environment as well as inside MediaWiki.
 *
 * @license GPL-2.0-or-later
 */
class BooleanParserTest extends TestCase {

	public function testImplementsValueParser(): void {
		$this->assertInstanceOf( ValueParser::class, new BooleanParser() );
	}

	/**
	 * @dataProvider trueValueProvider
	 */
	public function testParsesTrueValues( $rawValue ): void {
		$result = ( new BooleanParser() )->parse( $rawValue );

		$this->assertInstanceOf( BooleanValue::class, $result );
		$this->assertTrue( $result->getValue() );
	}

	public function trueValueProvider(): array {
		return [
			'native true' => [ true ],
			'string "true"' => [ 'true' ],
			'string "TRUE" (case-insensitive)' => [ 'TRUE' ],
			'string "1"' => [ '1' ],
			'string "yes"' => [ 'yes' ],
			'string "on"' => [ 'on' ],
			'string with surrounding whitespace' => [ '  true  ' ],
			'int 1' => [ 1 ],
		];
	}

	/**
	 * @dataProvider falseValueProvider
	 */
	public function testParsesFalseValues( $rawValue ): void {
		$result = ( new BooleanParser() )->parse( $rawValue );

		$this->assertInstanceOf( BooleanValue::class, $result );
		$this->assertFalse( $result->getValue() );
	}

	public function falseValueProvider(): array {
		return [
			'native false' => [ false ],
			'string "false"' => [ 'false' ],
			'string "FALSE" (case-insensitive)' => [ 'FALSE' ],
			'string "0"' => [ '0' ],
			'string "no"' => [ 'no' ],
			'string "off"' => [ 'off' ],
			'empty string' => [ '' ],
			'int 0' => [ 0 ],
		];
	}

	/**
	 * @dataProvider unparseableValueProvider
	 */
	public function testRejectsUnparseableValues( $rawValue ): void {
		$this->expectException( ParseException::class );

		( new BooleanParser() )->parse( $rawValue );
	}

	public function unparseableValueProvider(): array {
		return [
			'garbage string' => [ 'maybe' ],
			'numeric string other than 0/1' => [ '2' ],
			'int other than 0/1' => [ 2 ],
			'null' => [ null ],
			'array' => [ [ true ] ],
		];
	}

}
