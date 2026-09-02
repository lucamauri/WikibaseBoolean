# 2) Single PT:boolean entry, no separate VT:boolean layer {#adr_0002}

Date: 2026-09-02

## Status

**accepted**

## Context

Wikibase's data type registry distinguishes value types (`VT:*`, the
raw shape of a value) from property data types (`PT:*`, how a specific
property kind interprets that shape). Each `PT:*` entry declares a
`'value-type'`; if a callback (parser/formatter/validator/RDF-builder
factory) isn't defined on the `PT:*` entry itself, Wikibase falls back
to the one defined on the matching `VT:*` entry. This fallback is what
lets, for example, `PT:url` and `PT:string` share one string formatter
defined once under `VT:string`.

Wikibase core's own `lib/WikibaseLib.datatypes.php` was checked
directly: it declares exactly six value types (`string`,
`globecoordinate`, `monolingualtext`, `quantity`, `time`,
`wikibase-entityid`). No `boolean` value type exists anywhere in
Wikibase core. WikibaseBoolean introduces the first, and as far as this
project's current scope goes, only consumer of a boolean value type.

## Decision

Declare everything -- `'value-type' => 'boolean'` plus all four factory
callbacks -- directly on a single `'PT:boolean'` entry in
`WikibaseBoolean.datatypes.php`. No separate `'VT:boolean'` entry is
defined.

## Consequences

- Simpler to read and debug: one array entry, no fallback-resolution to
  trace when something doesn't work as expected.
- Consistent with this project's own explicit scope decision (a genuine
  2-state boolean, no `somevalue`/`novalue`, no generalizing ahead of
  actual need -- see CONTEXT.md).
- If a second boolean-flavored property data type is ever needed,
  splitting this into a `VT:boolean` + thin `PT:boolean` pair is a cheap
  refactor -- the callback bodies themselves don't need to change, only
  where they're registered. This ADR should be explicitly superseded if
  and when that split happens, not silently reversed.
