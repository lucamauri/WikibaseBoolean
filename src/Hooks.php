<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean;

/**
 * Hook handlers for WikibaseBoolean.
 *
 * FIXED (2026-09-18): this class previously declared
 * `implements Wikibase\Repo\Hooks\WikibaseRepoDataTypesHook`. That
 * interface does not exist anywhere in the installed Wikibase version --
 * confirmed directly against a live DataTrek install:
 *
 *   $ grep -rln "WikibaseRepoDataTypesHook" extensions/Wikibase/
 *   (no output)
 *
 * -- which made `class Hooks implements WikibaseRepoDataTypesHook` a hard
 * "Interface not found" fatal the moment this file was parsed, i.e. the
 * extension could never load at all. Confirmed on the same install that
 * the hook itself is real and is actually dispatched, just not through a
 * typed interface:
 *
 *   $ grep -n "WikibaseRepoDataTypes" extensions/Wikibase/repo/WikibaseRepo.ServiceWiring.php
 *   $services->getHookContainer()->run( 'WikibaseRepoDataTypes', [ &$dataTypes ] );
 *
 * -- a plain string-named hook, dispatched by HookContainer calling
 * `on<HookName>` on the registered handler by naming convention alone.
 * MediaWiki's HookContainer has never required a handler to implement a
 * matching interface for this to work; the interface, where one exists,
 * is optional type-safety sugar on top of that convention, not a runtime
 * requirement.
 *
 * The "constructible-handler-plus-interface" pattern this class was
 * originally trying to follow is real -- Wikibase's own
 * docs/adr/0029-use-hook-runner-hook-container.md genuinely states that
 * policy -- but confirmed (2026-09-18) that ADR is dated 2025-04-17 and
 * its migration evidently has not reached this particular hook yet: no
 * corresponding interface has actually been created for
 * `WikibaseRepoDataTypes` in the version installed here. Citing that ADR
 * as though the interface already existed, without checking, was the
 * same category of error this project has been burned by twice before
 * (a wrong ValueFormatter constant, a wrong ValueSnakRdfBuilder parameter
 * count) -- except this time it wasn't caught by reading vendored source
 * ahead of time, because the class-loading fatal only surfaces once the
 * extension is actually activated.
 *
 * Registered in extension.json under "HookHandlers" regardless -- that
 * registration style (and its "services" dependency-injection support,
 * if this class ever needs a MediaWiki service to build its data type
 * definitions) is unaffected by any of this; only the now-removed
 * `implements` clause was ever the problem.
 *
 * @license GPL-2.0-or-later
 */
class Hooks {

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
	 * Called by MediaWiki's HookContainer purely by name -- "on" followed
	 * by the hook name registered in extension.json's "Hooks" section
	 * ("WikibaseRepoDataTypes" => "main" => this class) -- see this
	 * class's own docblock for why no interface is needed for that to
	 * happen.
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