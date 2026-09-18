<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests;

use MediaWiki\Extension\WikibaseBoolean\Hooks;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Hooks
 *
 * FIXED (2026-09-18): this suite previously skip-guarded on
 * `interface_exists( 'Wikibase\Repo\Hooks\WikibaseRepoDataTypesHook' )`,
 * on the assumption that interface was only unavailable outside a real
 * MediaWiki + Wikibase installation. Confirmed directly on a live
 * DataTrek install (see Hooks.php's own docblock for the exact commands)
 * that the interface doesn't exist there either -- it doesn't exist
 * anywhere, full stop, because Hooks no longer implements it and no such
 * interface exists in the installed Wikibase version at all. That also
 * means the previous `testImplementsWikibaseRepoDataTypesHook()` test
 * had never once actually run, anywhere, in any environment -- it always
 * hit the skip guard, including inside real Wikibase, since the
 * interface it checked for was never there to begin with. It's removed
 * below rather than fixed, since there is no interface left to assert
 * against.
 *
 * With the interface reference gone from Hooks.php, this class has no
 * dependency on anything Wikibase- or MediaWiki-specific at all --
 * `onWikibaseRepoDataTypes()` only ever touches WikibaseBoolean's own
 * classes and the standalone-installable `DataValues\BooleanValue`. So
 * unlike BooleanRdfMapperTest (which genuinely still needs a live
 * Wikibase install for `ValueSnakRdfBuilder`/`PropertyValueSnak`), this
 * suite now runs for real under plain `composer test` -- no skip guard
 * needed at all. See phpunit.xml.dist's own header comment, updated to
 * match.
 *
 * @license GPL-2.0-or-later
 */
class HooksTest extends TestCase {

	public function testMergesBooleanDataTypeWithoutDisturbingExistingEntries(): void {
		$hooks = new Hooks();

		$dataTypeDefinitions = [
			'PT:string' => [ 'value-type' => 'string' ],
		];

		$hooks->onWikibaseRepoDataTypes( $dataTypeDefinitions );

		$this->assertArrayHasKey( 'PT:string', $dataTypeDefinitions );
		$this->assertArrayHasKey( 'PT:boolean', $dataTypeDefinitions );
	}

}