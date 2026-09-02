<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean;

use Wikibase\Repo\Hooks\WikibaseRepoDataTypesHook;

/**
 * Hook handlers for WikibaseBoolean.
 *
 * Registered in extension.json under "HookHandlers" (the modern,
 * constructible-handler-plus-interface pattern Wikibase itself recommends
 * in ADR-0029, rather than the older static-callback "Hooks" style). If
 * this class ever needs a MediaWiki service to build its data type
 * definitions, add it via a constructor parameter and list it under
 * "services" in the HookHandlers entry -- do not reach for global state.
 *
 * @license GPL-2.0-or-later
 */
class Hooks implements WikibaseRepoDataTypesHook {

	/**
	 * Merges WikibaseBoolean's data type definitions into Wikibase's
	 * repo-side data type registry.
	 *
	 * This is the single integration point between WikibaseBoolean and
	 * Wikibase: everything this extension contributes (the PT:boolean
	 * definition, and its parser/formatter/validator/RDF-mapper factory
	 * callbacks) flows through the array returned by
	 * WikibaseBoolean.datatypes.php, merged in here.
	 *
	 * @param array<string, array<string, mixed>> $dataTypeDefinitions
	 *   Passed by reference by Wikibase. Existing keys (from Wikibase core
	 *   and any other already-loaded extension) must be preserved --
	 *   this handler only ever adds to the array, never replaces it
	 *   wholesale.
	 */
	public function onWikibaseRepoDataTypes( array &$dataTypeDefinitions ): void {
		$booleanDataTypes = require __DIR__ . '/../WikibaseBoolean.datatypes.php';

		$dataTypeDefinitions = array_merge( $dataTypeDefinitions, $booleanDataTypes );
	}

}
