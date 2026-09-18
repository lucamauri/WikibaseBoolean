# 5) Expert module named `jquery.valueview.experts.Boolean`, not extension-prefixed {#adr_0005}

Date: 2026-09-17

## Status

**accepted**

## Context

Wikibase's per-statement edit widget is powered by `jQuery.valueview`.
A property data type opts into a custom edit widget by declaring an
`'expert-module'` key on its `PT:*` definition, naming a ResourceLoader
module that defines a `jQuery.valueview.Expert` subclass. Confirmed
(2026-09-17) against Wikibase's own `repo/WikibaseRepo.datatypes.php`
(both the public GitHub mirror and the installed copy on DataTrek):
every one of Wikibase core's own non-generic datatypes registers such a
module under the shared namespace `jquery.valueview.experts.<Name>`
(`...CommonsMediaType`, `...GeoShape`, `...StringValue`, and so on).

Nothing in ResourceLoader itself scopes that namespace to Wikibase
core -- module names are global across all loaded extensions, not
extension-scoped -- so a third-party extension registering its own
module there is not a technical violation of anything. The question is
purely one of convention: should WikibaseBoolean's checkbox widget be
named `jquery.valueview.experts.Boolean` (matching core's own style) or
something clearly extension-owned, e.g. `ext.wikibaseboolean.experts.Boolean`?

The first version of this decision (discussed in-session before this
ADR was written) leaned toward the prefixed name, reasoning that
squatting on a namespace that reads as "owned by Wikibase core" risked
a future collision if core ever shipped its own boolean/checkbox
expert. That reasoning was revised after checking a real precedent:
`ProfessionalWiki/WikibaseLocalMedia`, a currently-maintained,
independently-shipped third-party Wikibase datatype extension, adds its
own `localMedia` datatype with its own edit widget registered as
`'expert-module' => 'jquery.valueview.experts.LocalMediaType'` --
directly in the shared namespace, with a matching JS class name
`vv.experts.LocalMediaType`. This is a real, working, community
convention, not a hypothetical one.

## Decision

Register WikibaseBoolean's checkbox widget as
`jquery.valueview.experts.Boolean` (ResourceLoader module name) /
`vv.experts.Boolean` (JS class name), following WikibaseLocalMedia's
precedent, rather than an extension-prefixed name.

## Consequences

- Matches the real-world convention a contributor already familiar with
  Wikibase extension development would recognize immediately, rather
  than inventing a WikibaseBoolean-specific scheme.
- The theoretical collision risk this decision walks back is
  considered acceptably low specifically for `boolean`: Wikidata
  explicitly declined a Boolean datatype in
  [T145528](https://phabricator.wikimedia.org/T145528) (see
  CONTEXT.md), so Wikibase core shipping its own competing
  `jquery.valueview.experts.Boolean` in the future is very unlikely.
  This reasoning is specific to `boolean` and should not be read as a
  blanket "third-party experts should always use the shared namespace"
  rule for every future WikibaseBoolean-adjacent widget.
- If Wikibase core ever does add a boolean-flavoured expert under this
  same name, the resulting collision would need to be resolved by
  renaming WikibaseBoolean's module (a breaking change for any site
  relying on it), not by core deferring to us. This ADR should be
  revisited if that ever happens, rather than the situation being
  silently worked around.