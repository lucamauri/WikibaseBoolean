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

Early scaffold. Architecture and interfaces are stubbed out and documented;
no boolean logic is implemented yet. See `HANDOFF.md` for the current state
and open decisions.

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
