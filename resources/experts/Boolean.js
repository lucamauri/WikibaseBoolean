module.exports = ( function( $, vv ) {
	'use strict';

	var PARENT = vv.Expert;

	/**
	 * `Valueview` expert for the WikibaseBoolean data type: a checkbox that
	 * edits a value ultimately stored as a `DataValues\StringValue`
	 * (server-side) holding exactly the literal string "true" or "false".
	 *
	 * SUPERSEDED (2026-09-18): this class's docblock previously described
	 * reading the initial checkbox state via `this.viewState().value()`
	 * returning a `dataValues.BoolValue` instance, with `.getValue()`
	 * handing back a real JS boolean directly. That was correct as
	 * written, and the checkbox rendered exactly as designed -- but the
	 * underlying datatype could never actually be SAVED once built: a
	 * real, fully-traced bug in Wikibase core's client-side value-parsing
	 * round trip (wikibase.api.ParseValueCaller's `result.value &&
	 * result.type` truthy check, on a value that MediaWiki's own legacy
	 * JSON API rendering turns into an empty string for true and omits
	 * entirely for false) meant Save stayed disabled forever, for both
	 * checkbox states, with no thrown error anywhere. See
	 * manuals/adr/0006-boolean-as-string-value-type.md for the complete,
	 * file-by-file trace.
	 *
	 * The fix moved the underlying value from `dataValues.BoolValue` to
	 * `dataValues.StringValue` -- a non-empty string is always truthy in
	 * JS regardless of its content, so it survives the exact check that
	 * was silently failing before. The ONLY change this required here:
	 * `init()` below now reads `currentValue.getValue() === 'true'`
	 * instead of `currentValue.getValue()` directly, since
	 * `dataValues.StringValue#getValue()` returns the raw string, not a
	 * native boolean. `rawValue()` needed NO change at all -- it already
	 * returned the plain string 'true'/'false', by original design (see
	 * the reasoning below, unchanged from before this fix), which is
	 * exactly the shape the fix needed on the way back out too.
	 *
	 * This class still extends `jQuery.valueview.Expert` directly rather
	 * than `jQuery.valueview.experts.StringValue` -- even though the
	 * underlying value is now string-shaped server-side, the EDIT WIDGET
	 * is still a checkbox, not a text field, so there is still no
	 * text-editing behaviour from StringValue's own Expert worth
	 * inheriting.
	 *
	 * WRITING the value back out: `rawValue()` returns the plain string
	 * 'true' or 'false', never a constructed DataValue. `jQuery.valueview.
	 * Expert`'s own docblock explicitly discourages client-side parsing
	 * ("an already parsed DataValue object [...] but that should be
	 * avoided"). Returning a string means
	 * `MediaWiki\Extension\WikibaseBoolean\Parsers\BooleanParser` --
	 * already implemented, already tested, already accepting exactly
	 * "true"/"false" -- remains the one and only place a raw value is
	 * turned into the actual stored DataValue. Nothing here duplicates
	 * that logic.
	 *
	 * Registered under `jquery.valueview.experts.Boolean`, the shared (not
	 * extension-scoped) namespace Wikibase core's own Experts use -- see
	 * manuals/adr/0005-expert-module-naming.md; unaffected by this
	 * session's value-type change.
	 *
	 * NOT YET VERIFIED against a live edit (carried over from before this
	 * fix, still genuinely open): whether `viewState().value()` is
	 * reliably `null` (rather than throwing) for a brand-new statement
	 * that has no value yet. The `currentValue ? ... : false` guard below
	 * still assumes that.
	 *
	 * @class jQuery.valueview.experts.Boolean
	 * @extends jQuery.valueview.Expert
	 * @license GPL-2.0-or-later
	 */
	vv.experts.Boolean = vv.expert( 'Boolean', PARENT, function() {
		PARENT.apply( this, arguments );
		this.$input = $( '<input type="checkbox">' );
	}, {

		/**
		 * The checkbox DOM node itself. Unlike `StringValue`'s `$input`
		 * (a `<textarea>`), this is a native checkbox: no auto-expand and
		 * no debounced `eachchange` handling is needed, since a
		 * checkbox's native `change` event already fires exactly once per
		 * real state change -- there is no "typing" to debounce.
		 *
		 * @property {jQuery}
		 * @protected
		 * @readonly
		 */
		$input: null,

		/**
		 * @inheritdoc
		 *
		 * Overrides `init()` wholesale (rather than the `_init()` hook)
		 * because this Expert extends `vv.Expert` directly, same as
		 * `StringValue` does -- there is no lower-level Expert whose own
		 * `init()` already builds `this.$input` for us to layer on top of.
		 */
		init: function() {
			var notifier = this._viewNotifier,
				currentValue = this.viewState().value();

			this.$input
				.addClass( this.uiBaseClass + '-input' )
				// currentValue is expected to be null for a brand-new,
				// not-yet-saved statement -- default to unchecked. See
				// this class's docblock: NOT YET VERIFIED against a live
				// edit. currentValue.getValue() is now the raw string
				// 'true'/'false' (a dataValues.StringValue), not a native
				// boolean -- compare explicitly, don't rely on truthiness
				// of the string itself (see BooleanRdfMapper's docblock
				// for exactly why that distinction matters).
				.prop( 'checked', currentValue ? currentValue.getValue() === 'true' : false )
				.on( 'change', function() {
					notifier.notify( 'change' );
				} )
				.appendTo( this.$viewPort );

			PARENT.prototype.init.call( this );
		},

		/**
		 * @inheritdoc
		 */
		destroy: function() {
			if ( this.$input ) {
				this.$input.off( 'change' );
				this.$input = null;
			}

			PARENT.prototype.destroy.call( this ); // empties viewport
		},

		/**
		 * @inheritdoc
		 *
		 * Returns the plain string 'true' or 'false' -- unchanged by
		 * this session's fix; see this class's docblock for why this was
		 * already the right shape on the way out, even before the value
		 * type itself changed.
		 *
		 * @return {string}
		 */
		rawValue: function() {
			return this.$input.prop( 'checked' ) ? 'true' : 'false';
		},

		/**
		 * @inheritdoc
		 */
		draw: function() {
			this.$input.prop( 'disabled', this.viewState().isDisabled() );

			PARENT.prototype.draw.call( this );

			return $.Deferred().resolve().promise();
		},

		/**
		 * @inheritdoc
		 */
		focus: function() {
			this.$input.trigger( 'focus' );
		},

		/**
		 * @inheritdoc
		 */
		blur: function() {
			this.$input.trigger( 'blur' );
		}
	} );

	return vv.experts.Boolean;

}( jQuery, jQuery.valueview ) );