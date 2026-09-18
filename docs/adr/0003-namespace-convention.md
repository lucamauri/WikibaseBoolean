# 3) Namespace under MediaWiki\Extension\WikibaseBoolean, not Wikibase\Boolean {#adr_0003}

Date: 2026-09-02

## Status

**accepted**

## Context

Older Wikibase-ecosystem extensions (and some still-current ones)
namespace their code under a `Wikibase\<ThingName>\` prefix, mirroring
Wikibase core's own internal namespacing. Current MediaWiki-wide
convention for extensions in general, independent of Wikibase
specifically, is `MediaWiki\Extension\<ExtensionName>\`, which is what
MediaWiki core's own current developer documentation demonstrates for
new extensions.

## Decision

WikibaseBoolean's PSR-4 root namespace is
`MediaWiki\Extension\WikibaseBoolean\`, matching the current
MediaWiki-wide convention rather than the older Wikibase-specific
style.

## Consequences

- Consistent with how new MediaWiki extensions -- not just Wikibase
  ones -- are namespaced today; lower friction for contributors coming
  from the broader MediaWiki ecosystem rather than specifically from
  Wikibase.
- Slightly less obvious at a glance that this is a Wikibase-specific
  extension, compared to a `Wikibase\Boolean\` prefix. Mitigated by the
  `"type": "wikibase"` field in `extension.json` and by the project's
  name itself.
