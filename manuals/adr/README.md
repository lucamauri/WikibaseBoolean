# Architecture Decision Records

Architecture decisions for WikibaseBoolean are recorded here as ADRs,
following the same convention Wikibase core itself uses in its own
`docs/adr/` directory (Michael Nygard's ADR format: Title, Status,
Context, Decision, Consequences).

- One decision per file, named `NNNN-title-with-dashes.md`.
- ADRs are not deleted once accepted, even when a later decision changes
  course. Write a new ADR that supersedes the old one and reference it
  explicitly, rather than editing or removing the original -- the point
  of an ADR is to preserve *why* a past decision was made, including
  decisions later reversed.
- Status is one of: proposed, accepted, rejected, superseded.

## Index

- [0001 -- Reuse `DataValues\BooleanValue` instead of a custom `DataValue` class](0001-reuse-datavalues-booleanvalue.md)
- [0002 -- Single `PT:boolean` entry, no separate `VT:boolean` layer](0002-single-pt-boolean-entry.md)
- [0003 -- Namespace under `MediaWiki\Extension\WikibaseBoolean`](0003-namespace-convention.md)
- [0004 -- `BooleanMessageLookup` abstraction instead of calling `wfMessage()` directly](0004-boolean-message-lookup-abstraction.md)