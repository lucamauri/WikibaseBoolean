<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Formatters;

/**
 * Abstraction over MediaWiki's message system, so that BooleanFormatter can
 * be unit-tested with plain PHPUnit (no running MediaWiki install) while
 * still using real, wiki-overridable, translatable messages in production.
 *
 * WHY THIS EXISTS: BooleanFormatter needs to turn a true/false value into
 * localized text such as "True"/"False" (see wikibaseboolean-value-true/
 * -false in i18n/en.json). The natural way to do that in MediaWiki is the
 * global wfMessage() function, but wfMessage() only exists inside a
 * bootstrapped MediaWiki request -- calling it directly from
 * BooleanFormatter would mean BooleanFormatter (and therefore its test
 * suite) could no longer run under plain `composer test`/phpunit.xml.dist,
 * unlike BooleanParser. Depending on this interface instead of on
 * wfMessage() directly keeps BooleanFormatter's own logic (which message
 * key to pick, how to escape it per output format) testable in isolation,
 * with only the concrete MediaWiki-backed implementation
 * (MediaWikiBooleanMessageLookup) needing a real MediaWiki environment.
 *
 * DELIBERATELY returns a plain string, not a MediaWiki \Message object:
 * \Message is itself a MediaWiki core class. If this interface's method
 * returned \Message, a standalone PHPUnit fake implementing this interface
 * would still need \Message to exist for PHP to check interface
 * compliance at class-load time, silently reintroducing the same
 * standalone-testability problem this interface exists to solve. A plain
 * string keeps every implementation (real and fake) free of any
 * MediaWiki-only type.
 *
 * @license GPL-2.0-or-later
 */
interface BooleanMessageLookup {

	/**
	 * Returns the current wiki-language, already-substituted text for a
	 * message key.
	 *
	 * No HTML escaping is applied here -- that is deliberately left to the
	 * caller (BooleanFormatter), since whether escaping is needed at all
	 * depends on the output format being produced (plain/wikitext output
	 * must NOT be HTML-escaped; HTML output must be). Baking escaping into
	 * this method would force every implementation to guess the caller's
	 * intent.
	 *
	 * @param string $key A MediaWiki message key, e.g.
	 *   'wikibaseboolean-value-true'.
	 *
	 * @return string The message's rendered text, unescaped.
	 */
	public function getText( string $key ): string;

}