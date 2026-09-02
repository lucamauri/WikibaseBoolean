# 1) Reuse DataValues\BooleanValue instead of a custom DataValue class {#adr_0001}

Date: 2026-09-02

## Status

**accepted**

## Context

Wikibase's datatype architecture requires every property data type to
declare an underlying value type in the form of a `DataValues\DataValue`
implementation. WikibaseBoolean's initial technical-shape sketch (see
the project's CONTEXT.md) assumed a new, extension-owned `DataValue`
class would need to be written from scratch, following the shape of
other third-party Wikibase extensions.

Wikibase hard-depends on the `data-values/data-values` Composer package
for its own core value types. That package is not only an interface
definition -- its very first release (0.1.0, 2013) already shipped
trivial implementations, including `DataValues\BooleanValue`, alongside
`StringValue` and `NumberValue`. No later release (checked up to 3.1.1)
removed it.

Writing a new WikibaseBoolean-owned `DataValue` class would duplicate a
class that is already present, stable, and available in every
environment where WikibaseBoolean can be installed, since Wikibase
itself requires the package.

## Decision

Use `DataValues\BooleanValue` directly. WikibaseBoolean does not define
its own `DataValue` class. The `'PT:boolean'` datatype definition's
`'value-type'` is `'boolean'`, and every parser/formatter/validator/
RDF-mapper callback operates on `DataValues\BooleanValue` instances.

## Consequences

- One fewer class to write, document, and keep backward-compatible over
  time.
- WikibaseBoolean has no control over `BooleanValue`'s API if a future
  version of `data-values/data-values` changes it. A version constraint
  in `composer.json` mitigates, but does not eliminate, this coupling.
- No boolean-specific metadata beyond a plain `true`/`false` can be
  attached to the value object itself. If a future requirement needs
  that, it should be handled by a new ADR that explicitly supersedes
  this one, not by quietly wrapping `BooleanValue` later.
