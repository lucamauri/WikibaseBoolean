<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Formatters;

use DataValues\StringValue;
use InvalidArgumentException;
use MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanFormatter;
use PHPUnit\Framework\TestCase;
use ValueFormatters\ValueFormatter;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanFormatter
 *
 * SUPERSEDED (2026-09-18): constructed DataValues\BooleanValue before
 * this session's fix -- see BooleanFormatter's own docblock and
 * manuals/adr/0006-boolean-as-string-value-type.md. newTrueOrFalse()
 * below is the one place this suite converts a readable bool into the
 * actual StringValue shape BooleanFormatter now expects, so every
 * existing test case keeps its original, readable `true`/`false` data
 * provider values.
 *
 * Only depends on data-values/* and this extension's own
 * BooleanMessageLookup abstraction (via FakeBooleanMessageLookup) -- no
 * wfMessage() call happens on this path, so unlike Hooks/BooleanRdfMapper
 * this suite runs under plain `composer test`, same as BooleanParserTest.
 *
 * Uses BooleanFormatter::FORMAT_PLAIN/FORMAT_WIKI/FORMAT_HTML/etc.
 * throughout, not ValueFormatters\ValueFormatter's constants -- that
 * interface declares no format constants (confirmed against the installed
 * data-values/interfaces 1.2.0 source; it declares only OPT_LANG).
 *
 * @license GPL-2.0-or-later
 */
class BooleanFormatterTest extends TestCase {

	private static function newTrueOrFalse( bool $value ): StringValue {
		return new StringValue( $value ? 'true' : 'false' );
	}

	public function testImplementsValueFormatter(): void {
		$formatter = new BooleanFormatter( BooleanFormatter::FORMAT_PLAIN, new FakeBooleanMessageLookup() );

		$this->assertInstanceOf( ValueFormatter::class, $formatter );
	}

	/**
	 * @dataProvider plainAndWikiFormatProvider
	 */
	public function testPlainAndWikiFormatsReturnBareLocalizedTextWithNoGlyph(
		string $format,
		bool $rawValue,
		string $expectedText
	): void {
		$formatter = new BooleanFormatter( $format, new FakeBooleanMessageLookup() );

		$this->assertSame( $expectedText, $formatter->format( self::newTrueOrFalse( $rawValue ) ) );
	}

	public static function plainAndWikiFormatProvider(): array {
		return [
			'plain, true' => [ BooleanFormatter::FORMAT_PLAIN, true, 'True' ],
			'plain, false' => [ BooleanFormatter::FORMAT_PLAIN, false, 'False' ],
			'wiki, true' => [ BooleanFormatter::FORMAT_WIKI, true, 'True' ],
			'wiki, false' => [ BooleanFormatter::FORMAT_WIKI, false, 'False' ],
		];
	}

	/**
	 * @dataProvider glyphFormatProvider
	 */
	public function testHtmlAndWidgetFormatsPrefixTheGlyph(
		string $format,
		bool $rawValue,
		string $expectedText
	): void {
		$formatter = new BooleanFormatter( $format, new FakeBooleanMessageLookup() );

		$this->assertSame( $expectedText, $formatter->format( self::newTrueOrFalse( $rawValue ) ) );
	}

	public static function glyphFormatProvider(): array {
		return [
			'html, true' => [ BooleanFormatter::FORMAT_HTML, true, "\u{2713} True" ],
			'html, false' => [ BooleanFormatter::FORMAT_HTML, false, "\u{2717} False" ],
			'html widget, true' => [ BooleanFormatter::FORMAT_HTML_WIDGET, true, "\u{2713} True" ],
			'html widget, false' => [ BooleanFormatter::FORMAT_HTML_WIDGET, false, "\u{2717} False" ],
		];
	}

	public function testHtmlDiffFormatIsPlainTextWithNoGlyph(): void {
		$formatter = new BooleanFormatter( BooleanFormatter::FORMAT_HTML_DIFF, new FakeBooleanMessageLookup() );

		// No glyph prefix here -- see BooleanFormatter::GLYPH_FORMATS'
		// docblock for why FORMAT_HTML_DIFF is deliberately excluded.
		$this->assertSame( 'True', $formatter->format( self::newTrueOrFalse( true ) ) );
	}

	/**
	 * @dataProvider htmlEscapedFormatProvider
	 *
	 * Verifies actual escaping behaviour, not just glyph placement, by
	 * using a message override containing characters that HTML-escaping
	 * must neutralize. This simulates a wiki operator overriding
	 * MediaWiki:Wikibaseboolean-value-true with wikitext-unsafe or
	 * HTML-unsafe content.
	 */
	public function testHtmlFormatsEscapeUnsafeMessageText( string $format ): void {
		$messageLookup = new FakeBooleanMessageLookup( [
			'wikibaseboolean-value-true' => '<b>"Yes"</b> & more',
		] );
		$formatter = new BooleanFormatter( $format, $messageLookup );

		$result = $formatter->format( self::newTrueOrFalse( true ) );

		$this->assertStringNotContainsString( '<b>', $result );
		$this->assertStringContainsString( '&lt;b&gt;', $result );
		$this->assertStringContainsString( '&quot;', $result );
		$this->assertStringContainsString( '&amp;', $result );
	}

	public static function htmlEscapedFormatProvider(): array {
		return [
			'html' => [ BooleanFormatter::FORMAT_HTML ],
			'html widget' => [ BooleanFormatter::FORMAT_HTML_WIDGET ],
			'html diff' => [ BooleanFormatter::FORMAT_HTML_DIFF ],
		];
	}

	public function testPlainFormatDoesNotEscapeMessageText(): void {
		// Plain/wiki output must NOT be HTML-escaped -- it may feed
		// non-HTML consumers (the API, wikitext transclusion) where
		// &amp; in place of & would be actively wrong, not merely
		// unnecessary.
		$messageLookup = new FakeBooleanMessageLookup( [
			'wikibaseboolean-value-true' => 'A & B',
		] );
		$formatter = new BooleanFormatter( BooleanFormatter::FORMAT_PLAIN, $messageLookup );

		$this->assertSame( 'A & B', $formatter->format( self::newTrueOrFalse( true ) ) );
	}

	/**
	 * Confirms BooleanFormatter degrades to "false" display rather than
	 * throwing when handed a StringValue that isn't exactly "true" -- see
	 * BooleanFormatter::format()'s own docblock for why this class treats
	 * that as BooleanValidator's job, not its own, and normalizes instead
	 * of erroring.
	 */
	public function testTreatsAnyNonTrueStringAsFalse(): void {
		$formatter = new BooleanFormatter( BooleanFormatter::FORMAT_PLAIN, new FakeBooleanMessageLookup() );

		$this->assertSame( 'False', $formatter->format( new StringValue( 'not-a-valid-boolean-string' ) ) );
	}

	public function testRejectsNonStringValueInput(): void {
		$formatter = new BooleanFormatter( BooleanFormatter::FORMAT_PLAIN, new FakeBooleanMessageLookup() );

		$this->expectException( InvalidArgumentException::class );

		$formatter->format( 'not a StringValue' );
	}

}