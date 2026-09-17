<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Rdf;

use DataValues\BooleanValue;
use MediaWiki\Extension\WikibaseBoolean\Rdf\BooleanRdfMapper;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\Repo\Rdf\ValueSnakRdfBuilder;
use Wikimedia\Purtle\RdfWriter;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Rdf\BooleanRdfMapper
 *
 * Unlike HooksTest, this suite mocks RdfWriter directly against the real,
 * confirmed interface (vendor/wikimedia/purtle/src/RdfWriter.php) rather
 * than skip-guarding entirely -- but it still can't run under plain
 * `composer test`/phpunit.xml.dist, because merely loading
 * BooleanRdfMapper (and this test's own use of PropertyValueSnak/
 * PropertyId) requires Wikibase\Repo\Rdf\ValueSnakRdfBuilder and
 * Wikibase\DataModel\... to be autoloadable, which only holds inside a
 * real MediaWiki + Wikibase installation. See phpunit.xml.dist's own
 * header comment. Run via MediaWiki core's tests/phpunit/phpunit.php.
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
	 * @dataProvider booleanValueProvider
	 *
	 * Confirms addValue() writes a plain xsd:boolean literal via
	 * say()->value(), using the explicit 'true'/'false' lexical strings
	 * documented on BooleanRdfMapper -- never PHP's (string) cast of a
	 * bool, which would silently produce "1"/"" instead. $writer->say()
	 * and ->value() both return RdfWriter $this per the confirmed
	 * interface, so the mock chains via willReturnSelf().
	 */
	public function testAddValueWritesPlainXsdBooleanLiteral( bool $rawValue, string $expectedLexicalValue ): void {
		$writer = $this->createMock( RdfWriter::class );

		$writer->expects( $this->once() )
			->method( 'say' )
			->with( 'wdt', 'P1' )
			->willReturnSelf();

		$writer->expects( $this->once() )
			->method( 'value' )
			->with( $expectedLexicalValue, 'xsd', 'boolean' )
			->willReturnSelf();

		$snak = new PropertyValueSnak( new PropertyId( 'P1' ), new BooleanValue( $rawValue ) );

		( new BooleanRdfMapper() )->addValue( $writer, 'wdt', 'P1', 'boolean', 'wdv', $snak );
	}

	public static function booleanValueProvider(): array {
		return [
			'true becomes the literal string "true", never "1"' => [ true, 'true' ],
			'false becomes the literal string "false", never ""' => [ false, 'false' ],
		];
	}

}