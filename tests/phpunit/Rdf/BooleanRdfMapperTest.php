<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Rdf;

use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Rdf\BooleanRdfMapper
 *
 * Skeleton only, with an important caveat unique to this test (see
 * phpunit.xml.dist's top comment for the full explanation): unlike the
 * Parser/Formatter/Validator stubs, BooleanRdfMapper implements
 * Wikibase\Repo\Rdf\ValueSnakRdfBuilder -- an interface that lives inside
 * the Wikibase MediaWiki extension itself, not a standalone Composer
 * package. Merely loading the BooleanRdfMapper class file requires that
 * interface to already be autoloadable, which is only true inside a real
 * MediaWiki + Wikibase installation. Running this suite via plain
 * `composer test` outside such an environment will hit the guard below
 * and skip, rather than fatally erroring on class load.
 *
 * Once real logic exists, this test should be run via MediaWiki core's
 * own tests/phpunit/phpunit.php against an installation with
 * WikibaseRepository loaded, not via this standalone phpunit.xml.dist.
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
		$mapper = new \MediaWiki\Extension\WikibaseBoolean\Rdf\BooleanRdfMapper();

		$this->assertInstanceOf( \Wikibase\Repo\Rdf\ValueSnakRdfBuilder::class, $mapper );
	}

}
