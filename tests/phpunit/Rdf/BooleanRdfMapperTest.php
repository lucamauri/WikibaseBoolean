<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Rdf;

use DataValues\StringValue;
use MediaWiki\Extension\WikibaseBoolean\Rdf\BooleanRdfMapper;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\Repo\Rdf\ValueSnakRdfBuilder;
use Wikimedia\Purtle\RdfWriter;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Rdf\BooleanRdfMapper
 *
 * SUPERSEDED (2026-09-18): constructed DataValues\BooleanValue before
 * this session's fix -- see BooleanRdfMapper's own docblock and
 * manuals/adr/0006-boolean-as-string-value-type.md. The
 * 'defensively normalizes an unexpected string to "false"' case is new:
 * it exercises the exact-equality fix directly, since the old
 * `$value->getValue() ? 'true' : 'false'` ternary this class used to use
 * would have silently emitted "true" for a garbage string too (any
 * non-empty PHP string is truthy) -- this test would have failed against
 * that old code, which is the point of adding it now.
 *
 * Unlike BooleanParserTest/BooleanFormatterTest/BooleanValidatorTest,
 * this suite still can't run under plain `composer test`/phpunit.xml.dist:
 * merely loading BooleanRdfMapper (and this test's own use of
 * PropertyValueSnak/PropertyId) requires Wikibase\Repo\Rdf\
 * ValueSnakRdfBuilder and Wikibase\DataModel\... to be autoloadable,
 * which only holds inside a real MediaWiki + Wikibase installation. See
 * phpunit.xml.dist's own header comment. Run via MediaWiki core's
 * tests/phpunit/phpunit.php.
 *
 * @license GPL-2.0-or-later
 */
class BooleanRdfMapperTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		if ( !interface_exists( 'Wikibase\Repo\Rdf\ValueSnakRdfBuilder' ) ) {
			$this->markTestSkipped(
				'Wikibase\Repo\Rdf\ValueSnakRdfBuilder is not autoloadable in this ' .
				'environment. Run this test suite from within a MediaWiki + ' .
				'Wikibase installation instead of standalone Composer/PHPUnit.'
			);
		}
	}

	public function testImplementsValueSnakRdfBuilder(): void {
		$mapper = new BooleanRdfMapper();

		$this->assertInstanceOf( ValueSnakRdfBuilder::class, $mapper );
	}

	/**
	 * @dataProvider stringValueProvider
	 *
	 * Confirms addValue() writes a plain xsd:boolean literal via
	 * say()->value(), comparing the stored string by exact equality --
	 * never truthiness, which would silently mis-report "false" as
	 * "true" (see this class's own docblock). $writer->say() and
	 * ->value() both return RdfWriter $this per the confirmed interface,
	 * so the mock chains via willReturnSelf().
	 */
	public function testAddValueWritesPlainXsdBooleanLiteral( string $rawValue, string $expectedLexicalValue ): void {
		$writer = $this->createMock( RdfWriter::class );

		$writer->expects( $this->once() )
			->method( 'say' )
			->with( 'wdt', 'P1' )
			->willReturnSelf();

		$writer->expects( $this->once() )
			->method( 'value' )
			->with( $expectedLexicalValue, 'xsd', 'boolean' )
			->willReturnSelf();

		$snak = new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( $rawValue ) );

		( new BooleanRdfMapper() )->addValue( $writer, 'wdt', 'P1', 'boolean', 'wdv', $snak );
	}

	public static function stringValueProvider(): array {
		return [
			'"true" becomes the literal string "true"' => [ 'true', 'true' ],
			'"false" becomes the literal string "false", never "true" via truthiness' => [ 'false', 'false' ],
			'an unexpected string is defensively normalized to "false", not passed through' =>
				[ 'garbage', 'false' ],
		];
	}

}