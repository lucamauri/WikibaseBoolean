<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Formatters;

use DataValues\BooleanValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;

/**
 * Formats a DataValues\BooleanValue for display.
 *
 * Wikibase's formatter-factory-callback is invoked once per output format
 * it needs (plain text, HTML for the UI, wikitext for e.g. transclusion),
 * and is expected to return a formatter appropriate to that format -- see
 * the $format constructor parameter below, populated from Wikibase's own
 * ValueFormatters\FormatterOptions::OPTION_VALUE_FORMAT-style constants at
 * call time. This stub accepts that parameter so the shape survives
 * review, but does not yet branch on it.
 *
 * Concrete display questions deliberately left open for the next session
 * (not resolved by this scaffold):
 *   - What text represents true/false? Plain "true"/"false" is the safe
 *     default, but a fan-wiki deployment (per CONTEXT.md's target
 *     audience) may want this to be a message key so it's translatable
 *     and/or wiki-configurable (e.g. "Canon"/"Non-canon" instead of
 *     generic true/false for a specific property).
 *   - Should the HTML format render an icon/checkmark instead of text?
 *
 * @license GPL-2.0-or-later
 */
class BooleanFormatter implements ValueFormatter {

	/**
	 * @var string One of the ValueFormatters output format constants
	 *   (e.g. ValueFormatters\ValueFormatter::FORMAT_PLAIN,
	 *   FORMAT_HTML, FORMAT_WIKI). Stored, not yet acted on.
	 */
	private $format;

	/**
	 * @var FormatterOptions|null Reserved for future use (e.g. an option
	 *   controlling the true/false message keys mentioned above). Not
	 *   read anywhere yet.
	 */
	private $options;

	public function __construct( string $format, ?FormatterOptions $options = null ) {
		$this->format = $format;
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 *
	 * @param BooleanValue $value
	 *
	 * @return string The formatted representation of $value, in whatever
	 *   shape $this->format calls for. Not implemented yet.
	 */
	public function format( $value ) {
		throw new \LogicException( 'BooleanFormatter::format() is not implemented yet.' );
	}

}
