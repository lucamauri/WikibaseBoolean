# WikibaseBoolean

A native, true 2-state Boolean datatype for [Wikibase](https://www.mediawiki.org/wiki/Wikibase),
the structured-data MediaWiki extension that powers Wikidata and many
independent knowledge-base installs.

**This is not a proposal to change Wikidata.** Wikidata itself considered
and declined a Boolean datatype in
[T145528](https://phabricator.wikimedia.org/T145528), for reasons that make
sense at Wikidata's scale. WikibaseBoolean targets a different audience:
independent, standalone Wikibase deployments that don't have Wikidata's
federation constraints, and where many facts genuinely are atomic,
two-state flags that don't need item-level richness.

## Status

All four datatype components are implemented and tested:

- **Parser** (`BooleanParser`) -- raw input (checkbox state, or typed
  text like `true`/`yes`/`1`) to `DataValues\BooleanValue`.
- **Formatter** (`BooleanFormatter`) -- localized, format-aware display
  (plain, wikitext, HTML with a ✓/✗ glyph, HTML diff), via a
  `BooleanMessageLookup` abstraction so it's testable without a running
  MediaWiki instance.
- **Validator** (`BooleanValidator`) -- deliberately thin, per this
  project's 2-state scope decision: the only real failure mode is a
  mismatched registration handing it something other than a
  `BooleanValue`.
- **RDF mapper** (`BooleanRdfMapper`) -- maps to a plain `xsd:boolean`
  literal for the Wikibase Query Service.

`BooleanParser`, `BooleanFormatter`, and `BooleanValidator` run under
plain `composer test`. `BooleanRdfMapper` and the `Hooks` registration
class depend on Wikibase-internal interfaces and can only be
meaningfully tested inside a real MediaWiki + Wikibase installation
(see `phpunit.xml.dist`'s own header comment).

See `manuals/adr/` for the architecture decisions behind these
components and why they're shaped the way they are.

## Scope

- A genuine 2-state Boolean (`true`/`false`). No `somevalue`/`novalue` snak
  support -- this is a deliberate v1 scope decision, not an oversight.
- Standalone Wikibase installs only. No dependency on Wikidata Query
  Service internals or Wikidata core team buy-in.

## Requirements

- MediaWiki >= 1.43
- The Wikibase Repository extension

## License

GPL-2.0-or-later, matching Wikibase core and the rest of the Wikibase
extension ecosystem. See `LICENSE`.