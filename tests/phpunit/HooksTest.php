<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Hooks
 *
 * Same environment caveat as BooleanRdfMapperTest: Hooks implements
 * Wikibase\Repo\Hooks\WikibaseRepoDataTypesHook, which is only
 * autoloadable inside a MediaWiki + Wikibase installation.
 *
 * Once implemented, the meaningful test here is behavioural: call
 * onWikibaseRepoDataTypes() with a sample $dataTypeDefinitions array and
 * assert 'PT:boolean' is merged in without disturbing existing keys --
 * not just an instanceof check.
 *
 * @license GPL-2.0-or-later
 */
class HooksTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		if ( !interface_exists( 'Wikibase\Repo\Hooks\WikibaseRepoDataTypesHook' ) ) {
			$this->markTestSkipped(
				'Wikibase\Repo\Hooks\WikibaseRepoDataTypesHook is not autoloadable in ' .
				'this environment. Run this test suite from within a MediaWiki + ' .
				'Wikibase installation instead of standalone Composer/PHPUnit.'
			);
		}
	}

	public function testImplementsWikibaseRepoDataTypesHook(): void {
		$hooks = new \MediaWiki\Extension\WikibaseBoolean\Hooks();

		$this->assertInstanceOf(
			\Wikibase\Repo\Hooks\WikibaseRepoDataTypesHook::class,
			$hooks
		);
	}

	public function testMergesBooleanDataTypeWithoutDisturbingExistingEntries(): void {
		$hooks = new \MediaWiki\Extension\WikibaseBoolean\Hooks();

		$dataTypeDefinitions = [
			'PT:string' => [ 'value-type' => 'string' ],
		];

		$hooks->onWikibaseRepoDataTypes( $dataTypeDefinitions );

		$this->assertArrayHasKey( 'PT:string', $dataTypeDefinitions );
		$this->assertArrayHasKey( 'PT:boolean', $dataTypeDefinitions );
	}

}
