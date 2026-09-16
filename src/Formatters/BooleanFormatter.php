<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Formatters;

use DataValues\BooleanValue;
use InvalidArgumentException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;

/**
 * Formats a DataValues\BooleanValue for display.
 *
 * Wikibase invokes a fresh instance of this class (via
 * WikibaseBoolean.datatypes.php's 'formatter-factory-callback') once per
 * output format it needs. $format is a plain string identifying which one;
 * see this class's own FORMAT_* constants below (all five belong to
 * Wikibase's Wikibase\Lib\Formatters\SnakFormatter convention, not to
 * data-values/interfaces' ValueFormatter -- that interface declares no
 * format constants at all, confirmed against the installed source). See
 * this class's HTML_FORMATS
 * and GLYPH_FORMATS constants below for exactly which formats get which
 * treatment, and why.
 *
 * DESIGN: text lookup is delegated to a BooleanMessageLookup rather than
 * calling wfMessage() directly. This keeps BooleanFormatter's own logic --
 * which message key to use, whether to prefix a glyph, whether to escape --
 * testable with plain PHPUnit, with only the production
 * MediaWikiBooleanMessageLookup implementation needing a real MediaWiki
 * environment. See BooleanMessageLookup's own docblock for the full
 * rationale.
 *
 * @license GPL-2.0-or-later
 */
class BooleanFormatter implements ValueFormatter {

	/**
	 * Output format identifiers Wikibase invokes this formatter with.
	 *
	 * CORRECTED (2026-09-12): these were originally written as references
	 * to ValueFormatters\ValueFormatter::FORMAT_PLAIN / FORMAT_HTML, on the
	 * assumption that data-values/interfaces' ValueFormatter interface
	 * declared them. It does not -- confirmed directly against the
	 * installed data-values/interfaces 1.2.0 source
	 * (vendor/data-values/interfaces/src/ValueFormatters/ValueFormatter.php
	 * declares exactly one constant, OPT_LANG, and nothing else). This was
	 * a real bug, not a style choice: it surfaced as "Undefined constant"
	 * errors the first time the test suite actually ran.
	 *
	 * All five of these format identifiers actually belong to Wikibase's
	 * own Wikibase\Lib\Formatters\SnakFormatter interface, which is not a
	 * Composer-installable package -- it lives inside the Wikibase
	 * MediaWiki extension itself, the same reason BooleanRdfMapper and
	 * Hooks can't be loaded outside a real MediaWiki+Wikibase install (see
	 * their own docblocks). BooleanFormatter therefore cannot reference
	 * SnakFormatter::FORMAT_HTML etc. at compile time; it defines its own
	 * copies of the same string literals instead, matching Wikibase's
	 * documented convention.
	 *
	 * STILL NOT CONFIRMED against the installed Wikibase version's actual
	 * Wikibase\Lib\Formatters\SnakFormatter source this session -- these
	 * string values are reconstructed from established Wikibase
	 * convention, not read from a file this session. Given that the
	 * FORMAT_PLAIN/FORMAT_HTML assumption above was already wrong once,
	 * this is a good candidate to verify next time you're in the vendored
	 * Wikibase source: `cat` the real SnakFormatter.php and compare its
	 * constants against the five below.
	 */
	public const FORMAT_PLAIN = 'text/plain';
	public const FORMAT_WIKI = 'text/x-wiki';
	public const FORMAT_HTML = 'text/html';
	public const FORMAT_HTML_WIDGET = 'text/html; disposition=widget';
	public const FORMAT_HTML_DIFF = 'text/html; disposition=diff';

	/**
	 * Formats that get the ✓/✗ glyph prefixed ahead of the localized
	 * true/false text.
	 *
	 * FORMAT_HTML_DIFF is deliberately excluded: Wikibase's own diff view
	 * already uses background color to mark added/removed statement
	 * values, and layering a second glyph/color convention on top of that
	 * risks visually fighting the diff chrome rather than clarifying it
	 * (see prior discussion -- this was an explicit scoping decision, not
	 * an oversight).
	 */
	private const GLYPH_FORMATS = [
		self::FORMAT_HTML,
		self::FORMAT_HTML_WIDGET,
	];

	/**
	 * Formats whose output is HTML and therefore must be HTML-escaped.
	 * Includes FORMAT_HTML_DIFF even though it doesn't get the glyph
	 * prefix above -- it is still HTML output, just without the extra
	 * marker.
	 */
	private const HTML_FORMATS = [
		self::FORMAT_HTML,
		self::FORMAT_HTML_WIDGET,
		self::FORMAT_HTML_DIFF,
	];

	private const GLYPH_TRUE = '✓';
	private const GLYPH_FALSE = '✗';

	/** @var string One of the format constants documented above. */
	private string $format;

	/** @var BooleanMessageLookup Resolves message keys to localized text. */
	private BooleanMessageLookup $messageLookup;

	/**
	 * @var FormatterOptions|null Reserved for future use (e.g. a
	 *   configurable glyph on/off toggle, if that's ever wanted per-property
	 *   rather than globally). Not read anywhere yet -- kept only so the
	 *   constructor shape doesn't need to change again if that need arises.
	 */
	private $options;

	public function __construct(
		string $format,
		BooleanMessageLookup $messageLookup,
		?FormatterOptions $options = null
	) {
		$this->format = $format;
		$this->messageLookup = $messageLookup;
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 *
	 * @param BooleanValue $value
	 *
	 * @return string The formatted representation of $value: localized
	 *   "True"/"False" text (see wikibaseboolean-value-true/-false in
	 *   i18n/en.json), optionally glyph-prefixed and/or HTML-escaped
	 *   depending on $this->format -- see GLYPH_FORMATS and HTML_FORMATS.
	 *
	 * @throws InvalidArgumentException If $value is not a BooleanValue.
	 *   Wikibase's own formatter dispatch is expected to only ever hand
	 *   PT:boolean snaks to this class, so this is a defensive guard
	 *   against a mismatched registration, not an expected runtime path.
	 */
	public function format( $value ) {
		if ( !( $value instanceof BooleanValue ) ) {
			throw new InvalidArgumentException(
				'BooleanFormatter can only format DataValues\BooleanValue instances, got '
					. ( is_object( $value ) ? get_class( $value ) : gettype( $value ) ) . '.'
			);
		}

		$isTrue = $value->getValue();
		$messageKey = $isTrue ? 'wikibaseboolean-value-true' : 'wikibaseboolean-value-false';
		$text = $this->messageLookup->getText( $messageKey );

		if ( in_array( $this->format, self::GLYPH_FORMATS, true ) ) {
			$glyph = $isTrue ? self::GLYPH_TRUE : self::GLYPH_FALSE;
			$text = $glyph . ' ' . $text;
		}

		if ( in_array( $this->format, self::HTML_FORMATS, true ) ) {
			// ENT_QUOTES: escape both single and double quotes, not just
			// double -- the message text is wiki-configurable (see
			// BooleanMessageLookup's docblock), so a wiki operator could
			// introduce a quote character via a MediaWiki:Wikibaseboolean-
			// value-true override, and this output may end up inside an
			// HTML attribute as well as element content.
			return htmlspecialchars( $text, ENT_QUOTES );
		}

		return $text;
	}

}