<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Formatters;

/**
 * Real, production implementation of BooleanMessageLookup, backed by
 * MediaWiki's own message system (wfMessage()).
 *
 * This is the implementation that WikibaseBoolean.datatypes.php's
 * 'formatter-factory-callback' wires up in an actual running wiki. It is
 * intentionally the *only* class in this extension allowed to call
 * wfMessage() for boolean-value text -- see BooleanMessageLookup's own
 * docblock for why that call is isolated here rather than inlined into
 * BooleanFormatter.
 *
 * NOT covered by phpunit.xml.dist's standalone suite: like Hooks and
 * BooleanRdfMapper, this class depends on a MediaWiki global function that
 * does not exist outside a bootstrapped MediaWiki request, so it can only
 * be meaningfully exercised via MediaWiki core's own
 * tests/phpunit/phpunit.php runner, not plain `composer test`.
 *
 * @license GPL-2.0-or-later
 */
class MediaWikiBooleanMessageLookup implements BooleanMessageLookup {

	/**
	 * @inheritDoc
	 *
	 * Delegates to wfMessage($key)->text(), which resolves the message
	 * through MediaWiki's normal i18n pipeline: current interface language,
	 * any on-wiki MediaWiki:Wikibaseboolean-value-true/false override, and
	 * standard parameter substitution (unused here, since these messages
	 * take no parameters, but ->text() handles it if that ever changes).
	 *
	 * ->text() specifically (not ->parse() or ->escaped()) is used because
	 * this method's contract, per the interface, is to return unescaped
	 * text -- callers decide separately whether HTML-escaping is needed.
	 */
	public function getText( string $key ): string {
		return wfMessage( $key )->text();
	}

}