# 4) BooleanMessageLookup abstraction instead of calling wfMessage() directly {#adr_0004}

Date: 2026-09-12

## Status

**accepted**

## Context

`BooleanFormatter::format()` needs to turn a `DataValues\BooleanValue` into
localized text ("True"/"False", via the `wikibaseboolean-value-true` and
`wikibaseboolean-value-false` message keys in `i18n/en.json`). The natural,
idiomatic way to resolve a message key to text in MediaWiki is the global
`wfMessage()` function.

`wfMessage()` only exists inside a bootstrapped MediaWiki request. Calling
it directly from `BooleanFormatter` would mean `BooleanFormatter` -- and
therefore `BooleanFormatterTest` -- could no longer run under plain
`composer test`/`phpunit.xml.dist`, the way `BooleanParserTest` currently
does. It would move into the same category as `HooksTest` and
`BooleanRdfMapperTest`: skipped outside a full MediaWiki + Wikibase
installation, and only meaningfully exercised via MediaWiki core's own
`tests/phpunit/phpunit.php` runner.

`phpunit.xml.dist`'s own header comment currently states that
`BooleanFormatter` is expected to be standalone-testable, alongside
`BooleanParser` and `BooleanValidator`. Calling `wfMessage()` directly
would silently break that documented assumption.

## Decision

Introduce a `BooleanMessageLookup` interface (`src/Formatters/`) with a
single method, `getText( string $key ): string`, returning already-localized,
unescaped message text. `BooleanFormatter`'s constructor takes a
`BooleanMessageLookup` instance rather than resolving messages itself.

Two implementations exist:

- `MediaWikiBooleanMessageLookup` -- the production implementation, backed
  by `wfMessage( $key )->text()`. Wired up in
  `WikibaseBoolean.datatypes.php`'s `'formatter-factory-callback'`, the one
  place where WikibaseBoolean is definitely running inside a real MediaWiki
  request.
- `FakeBooleanMessageLookup` (`tests/phpunit/Formatters/`) -- an in-memory
  test double used only by `BooleanFormatterTest`, returning configurable
  fixed strings for known keys.

The interface's method deliberately returns a plain `string`, not a
MediaWiki `\Message` object. `\Message` is itself a MediaWiki-only class;
if the interface's return type were `\Message`, a standalone PHPUnit fake
implementing this interface would still require `\Message` to be
resolvable for PHP to verify interface compliance at class-load time,
reintroducing the exact standalone-testability problem this ADR exists to
solve. HTML-escaping of the returned text (needed for `FORMAT_HTML` and
related formats, not for `FORMAT_PLAIN`/`FORMAT_WIKI`) is therefore left to
`BooleanFormatter` itself, via plain `htmlspecialchars()`, rather than to
`\Message::escaped()`.

## Consequences

- `BooleanFormatterTest` continues to run under plain `composer test`,
  consistent with `phpunit.xml.dist`'s documented scope -- no test needs
  moving into the MediaWiki-only category as a side effect of implementing
  the formatter.
- One additional interface and two small implementing classes exist that
  would not exist if `BooleanFormatter` called `wfMessage()` directly. This
  is judged a worthwhile trade for keeping the formatter's own logic
  (message key selection, glyph prefixing, escaping) fast and easy to test
  in isolation.
- Any future formatter-adjacent code that needs localized text (for
  example, if a configurable message-key override per property is ever
  added) should go through `BooleanMessageLookup` rather than reintroducing
  a direct `wfMessage()` call, to keep this boundary consistent.
- If `BooleanMessageLookup` ever needs parameter substitution (MediaWiki's
  `$1`/`$2`-style message parameters), its `getText()` signature will need
  to grow a `$params` argument -- not anticipated for a 2-state boolean's
  fixed true/false text, but noted here so it isn't a surprise later.