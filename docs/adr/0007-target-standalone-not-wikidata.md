# 7) Target standalone Wikibase installs, not Wikidata {#adr_0007}

Date: 2026-09-20

## Status

**accepted**

## Context

Wikidata itself considered and declined a Boolean datatype, in
[T145528](https://phabricator.wikimedia.org/T145528), recommending an
item-datatype workaround instead (linking to "true value" / "false
value" items). That decision makes sense at Wikidata's scale:
item-based booleans support qualifiers, ranks, references, and
multilingual labels, and Wikidata's Query Service and tooling ecosystem
would bear a large, permanent maintenance cost for a new datatype
across a federation of that size.

WikibaseBoolean's actual audience is different: independent, standalone
Wikibase deployments (starting with DataTrek) that don't have
Wikidata's federation constraints, and where many facts genuinely are
atomic, two-state flags -- e.g. "is this a canon episode," "is this
character a hologram" -- that don't need item-level richness.

This reasoning originally lived only in CONTEXT.md (an internal
planning document) and, duplicated, as a defensive disclaimer at the
top of the public README and the mediawiki.org extension page ("this
is not a proposal to change Wikidata..."). On review, that phrasing was
answering an objection relevant to Wikidata's own core developers, not
to WikibaseBoolean's actual readers -- someone running their own
standalone Wikibase doesn't need to be told this isn't a pitch to a
team they aren't part of. It was also redundant with the plain scope
statement already present elsewhere in the same documents. The
disclaimer was removed from both public-facing documents; this ADR is
now the durable record of the reasoning behind the scope decision
itself, which the disclaimer had been standing in for.

## Decision

WikibaseBoolean targets standalone Wikibase installations only. It does
not seek Wikidata core team buy-in, does not depend on Wikidata Query
Service internals, and does not treat T145528 as a decision open for
reconsideration. Reader-facing documentation states the scope as a
plain fact (a bullet under "Scope" / a feature description), without a
"this is not a proposal" preamble.

## Consequences

- Public documentation reads as a description of what the extension
  is, rather than a rebuttal of an objection the actual reader wasn't
  raising.
- The T145528 precedent and its reasoning remain discoverable -- here,
  and via the existing references to it in
  [ADR-0002](0002-single-pt-boolean-entry.md) and
  [ADR-0005](0005-expert-module-naming.md) -- without being repeated as
  reader-facing prose in the README, the landing page, and the
  mediawiki.org page.
- If WikibaseBoolean's scope ever changes to pursue Wikidata
  integration, that would be a decision significant enough to warrant
  its own ADR explicitly superseding this one, not a quiet rewording of
  a README paragraph.