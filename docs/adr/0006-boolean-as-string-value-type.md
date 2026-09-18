# 6) Store the boolean value as DataValues\StringValue ('true'/'false'), not DataValues\BooleanValue {#adr_0006}

Date: 2026-09-18

## Status

**accepted** -- supersedes [ADR-0001](0001-reuse-datavalues-booleanvalue.md)

## Context

The first time WikibaseBoolean's checkbox widget was actually exercised
live (creating a test property and statement on DataTrek), the Save
button stayed permanently disabled -- for both checkbox states, with no
JavaScript exception thrown anywhere. The cause was traced fully, file
by file, against real source on both the client and server side:

1. Toggling the checkbox correctly fires `Boolean.js`'s `change`
   notification, which triggers Wikibase's generic `jquery.valueview`
   machinery to re-parse the raw value server-side via
   `action=wbparsevalue`. This round trip is mandatory and generic --
   every Expert goes through it, not something specific to this
   extension's widget.
2. Server-side, `BooleanParser` correctly parsed the value into a
   `DataValues\BooleanValue`. Wikibase core's
   `repo/includes/Api/ParseValue.php` (`ParseValue::parseStringValue()`)
   puts that value's `getArrayValue()` -- a raw PHP `bool` -- into the
   API result's `value` field.
3. MediaWiki's own default ("BC") JSON API format renders a raw PHP
   `true` as the empty string `""`, and omits `false` from the response
   entirely. This is long-standing, documented MediaWiki API behaviour,
   unrelated to Wikibase, present because the calling code doesn't
   request `formatversion=2`.
4. Confirmed as the actual bug: Wikibase core's own
   `lib/resources/packages/wikibase-api/src/ParseValueCaller.js`
   (`parseValues()`) checks `if ( !( result.value && result.type ) )` and
   treats a falsy `result.value` as an unknown API error. `""` is falsy.
   A missing key is falsy. So both `true` and `false` are rejected as
   errors, unconditionally, every time -- silently, since a rejected
   promise throws nothing.
5. That rejection propagates up through
   `view/resources/jquery/wikibase/snakview/snakview.variations.Value.js`
   (`self._viewState.notify( self._valueView.value() ? 'valid' :
   'invalid' )`) as "invalid", disabling Save.

This is a genuine bug in Wikibase core, not in anything this extension
wrote. It appears to be dormant since Wikibase core's own creation: every
datatype core has ever shipped serializes its valid, meaningful value as
a non-empty string or a structured array/object, both always JS-truthy
regardless of content. A raw boolean is the first value shape whose
entire meaningful range includes a literal falsy value. Wikidata's own
decision not to add a Boolean datatype
([T145528](https://phabricator.wikimedia.org/T145528)) means nobody in
the wider Wikibase ecosystem had ever exercised this exact code path with
a real boolean before.

Two categories of fix were considered and rejected before this one:

- **Patching the bug at its source** (Wikibase core's `ParseValueCaller.js`,
  either by editing the live vendored file or by registering a
  same-named ResourceLoader module that overrides it). Rejected: fragile
  against any Wikibase upgrade, and modifying or shadowing another
  extension's core file from within WikibaseBoolean is exactly the kind
  of change this project's own workflow treats with extra caution on a
  production instance.
- **Keeping `DataValues\BooleanValue` but overriding just its
  `getArrayValue()`** to return a string while leaving `'value-type' =>
  'boolean'` in place. Investigated and rejected: the client-side
  `dataValues.BoolValue` JS class (also Wikibase-core, not ours) has its
  own strict `typeof value !== 'boolean'` constructor guard. Feeding it
  a string throws synchronously the moment the widget tries to read the
  value back -- trading one bug for a worse one.

## Decision

`BooleanParser` now returns a `DataValues\StringValue` holding exactly
the literal string `'true'` or `'false'` (see the new `BooleanStrings`
class, `src/BooleanStrings.php`, for the single source of truth for
those two strings). `WikibaseBoolean.datatypes.php` declares `'value-type'
=> 'string'` instead of `'boolean'`, and no longer declares its own
`'deserializer-builder'` at all -- confirmed against
`lib/includes/DataTypeDefinitions.php` that this correctly falls back to
Wikibase core's own already-registered `VT:string` deserializer
(`StringValue::class`), the same PT-falls-back-to-VT mechanism
[ADR-0002](0002-single-pt-boolean-entry.md) already relies on for other
fields.

This works because a non-empty string is always truthy in JavaScript
regardless of its content -- `'false'` the string is just as truthy as
`'true'` -- so both values now survive `ParseValueCaller.js`'s check
unmodified, and because the client resolves `'string'`-typed values via
`dataValues.StringValue`, which accepts any string unconditionally,
never routing through the strict `dataValues.BoolValue` at all.

`BooleanFormatter`, `BooleanValidator`, and `BooleanRdfMapper` were all
updated to read a `StringValue` instead of a `BooleanValue`, comparing
its content by exact string equality (`=== BooleanStrings::TRUE`) rather
than relying on native boolean typing. `resources/experts/Boolean.js` was
updated the same way for reading the checkbox's initial state.

## Consequences

- `BooleanValidator`'s job description changes, not just its
  implementation. Its entire previous rationale was that there was
  nothing meaningful left to validate, because a native PHP bool is
  inherently 2-state. A string is not -- it can be anything -- so this
  validator is now the one thing standing between "any string" and "a
  genuine 2-state boolean". This is a real increase in responsibility,
  not a rename.
- `BooleanRdfMapper` (and, on the client, `Boolean.js`) each had a latent
  version of the *exact same class of bug* this ADR is about, just in
  PHP/JS truthiness rather than Wikibase's API: a ternary like
  `$value->getValue() ? 'true' : 'false'` is wrong the moment
  `getValue()` can return the string `"false"`, since any non-empty
  string is truthy. Both were fixed to compare by exact equality
  instead, at the same time as this change, rather than being left as a
  dormant bug waiting for its own bad day.
- WikibaseBoolean now depends, informally, on Wikibase core's `VT:string`
  registration continuing to exist and behave as it does today. This is
  a reasonable dependency -- string is one of Wikibase's oldest, most
  fundamental value types, effectively certain to remain stable -- but
  it is a dependency that didn't exist before this ADR.
- Nothing about RDF export's actual OUTPUT changes: `BooleanRdfMapper`
  still explicitly writes a genuine `xsd:boolean`-typed literal
  (`"true"^^xsd:boolean` / `"false"^^xsd:boolean`), completely
  independent of the PHP-side value's own class. Query Service consumers
  see no difference.
- Since Save had never once succeeded before this fix, there was no live
  data anywhere in the old shape to migrate. This ADR was adopted at the
  cleanest possible moment for exactly that reason -- this reasoning does
  NOT extend to any future WikibaseBoolean-adjacent value-shape change
  once real data exists in production.
- If Wikibase core ever fixes the underlying `ParseValueCaller.js` bug
  this ADR works around, that fix would not, by itself, give any reason
  to revert this ADR: the string-based representation has no known
  downside independent of the bug it was originally chosen to dodge, and
  reverting would just reintroduce a second, unrelated risk (a future
  Wikibase upgrade tightening `dataValues.BoolValue`'s guard further, for
  instance) for no benefit.