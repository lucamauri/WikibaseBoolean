# WikibaseBoolean

**[lucamauri.github.io/WikibaseBoolean](https://lucamauri.github.io/WikibaseBoolean/)**

A native, true 2-state Boolean datatype for [Wikibase](https://www.mediawiki.org/wiki/Wikibase),
the structured-data MediaWiki extension that powers Wikidata and many
independent knowledge-base installs, for standalone deployments that
want a lightweight true/false flag without maintaining a pair of
"true value" / "false value" items for every such property.

## Status

All four datatype components are implemented and tested, plus a checkbox
edit widget and a CI pipeline that exercises the extension inside a real,
disposable MediaWiki + Wikibase install:

- **Parser** (`BooleanParser`) -- raw input (checkbox state, or typed
  text like `true`/`yes`/`1`) to a `DataValues\StringValue` holding
  exactly the literal string `"true"` or `"false"` (see
  [ADR-0006](docs/adr/0006-boolean-as-string-value-type.md) for why the
  underlying value type is a string, not a native `DataValues\BooleanValue`).
- **Formatter** (`BooleanFormatter`) -- localized, format-aware display
  (plain, wikitext, HTML with a ✓/✗ glyph, HTML diff), via a
  `BooleanMessageLookup` abstraction so it's testable without a running
  MediaWiki instance.
- **Validator** (`BooleanValidator`) -- checks that a value is genuinely
  a `StringValue` holding exactly `"true"` or `"false"` and nothing
  else. This is real validation, not a formality: once the underlying
  value type is a plain string rather than a native bool, the type
  system no longer guarantees a stored value is well-formed on its own
  (see ADR-0006 and this class's own docblock).
- **RDF mapper** (`BooleanRdfMapper`) -- maps to a plain `xsd:boolean`
  literal for the Wikibase Query Service, comparing by exact string
  equality rather than truthiness (a plain truthy check would silently
  mis-report every stored `"false"` as RDF `"true"`, since any
  non-empty PHP string is truthy).
- **Edit widget** (`resources/experts/Boolean.js`, registered as
  `jquery.valueview.experts.Boolean` -- see
  [ADR-0005](docs/adr/0005-expert-module-naming.md)) -- a checkbox
  widget for editing statement values directly, rather than a raw text
  field.

`BooleanParser`, `BooleanFormatter`, `BooleanValidator`, and `Hooks` run
under plain `composer test`. `BooleanRdfMapper` depends on
Wikibase-internal interfaces and can only be meaningfully tested inside
a real MediaWiki + Wikibase installation (see `phpunit.xml.dist`'s own
header comment) -- CI covers this via a disposable install against both
MediaWiki `REL1_43` (this extension's minimum supported version) and
`master` (an allowed-to-fail canary for upstream breakage).

Live-tested end to end on a real Wikibase deployment (DataTrek): a
Boolean property with a saved statement, verified correct through to
RDF export (`xsd:boolean`, via `Special:EntityData`).

See [`docs/adr/`](docs/adr/) for the architecture decisions behind these
components and why they're shaped the way they are.

## Scope

- A genuine 2-state Boolean (`true`/`false`). No `somevalue`/`novalue` snak
  support -- this is a deliberate v1 scope decision, not an oversight.
- Standalone Wikibase installs only. No dependency on Wikidata Query
  Service internals or Wikidata core team buy-in.

## Requirements

- MediaWiki >= 1.43
- PHP >= 8.1
- The Wikibase Repository extension

## Installation

First, install MediaWiki and the Wikibase Repository extension.

### Using Composer (recommended)

From your wiki's root directory:

```shell
COMPOSER=composer.local.json composer require --no-update lucamauri/wikibase-boolean
composer update lucamauri/wikibase-boolean --no-dev -o
```

### Manual download

Clone or download this repository, and place the `WikibaseBoolean`
directory into your wiki's `extensions/` folder. You will also need to
merge WikibaseBoolean's own Composer requirements
(`data-values/data-values`, `data-values/interfaces`) into MediaWiki's
`vendor/` -- add an `"include"` entry for
`extensions/WikibaseBoolean/composer.json` to your wiki's
`composer.local.json` `merge-plugin` configuration, then run
`composer update` from your wiki's root.

### Enabling the extension

Add the following to the bottom of `LocalSettings.php`, after Wikibase
Repository is loaded:

```php
wfLoadExtension( 'WikibaseBoolean' );
```

Verify the extension is active via `Special:Version`. A "Boolean" data
type will then be available when creating a new property.

## Localization

`i18n/en.json` (source) and `i18n/it.json` (Italian) are included.
Contributions of further translations are welcome via MediaWiki's
standard `i18n/` message-file convention.

## License

GPL-2.0-or-later, matching Wikibase core and the rest of the Wikibase
extension ecosystem. See `LICENSE`.

---

Forged with logic 🖖🏻 at [Wikitrek](https://wikitrek.org), shared freely with the Galaxy