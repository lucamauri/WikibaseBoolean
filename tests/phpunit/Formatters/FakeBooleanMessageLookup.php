<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Formatters;

use MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanMessageLookup;

/**
 * In-memory fake for BooleanMessageLookup, used only by BooleanFormatterTest.
 *
 * WHY A FAKE RATHER THAN A MOCKING FRAMEWORK: BooleanMessageLookup has a
 * single trivial method, so a real class implementing it is simpler and
 * more readable in test failure output than a configured mock object.
 * Defaults are chosen to match the real i18n/en.json message text
 * ("True"/"False"), so tests that don't care about exact wording can
 * construct this with no arguments; tests that need to verify escaping
 * behaviour can override individual keys via the constructor.
 *
 * @license GPL-2.0-or-later
 */
class FakeBooleanMessageLookup implements BooleanMessageLookup {

	/** @var array<string, string> Message key => text to return for it. */
	private array $texts;

	/**
	 * @param array<string, string> $overrides Message key => text,
	 *   overriding the built-in "True"/"False" defaults. Use this to
	 *   simulate, for example, a wiki-configured message containing
	 *   characters that need HTML-escaping.
	 */
	public function __construct( array $overrides = [] ) {
		$this->texts = $overrides + [
			'wikibaseboolean-value-true' => 'True',
			'wikibaseboolean-value-false' => 'False',
		];
	}

	public function getText( string $key ): string {
		return $this->texts[$key] ?? $key;
	}

}