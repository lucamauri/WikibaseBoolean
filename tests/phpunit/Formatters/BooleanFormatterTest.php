<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Formatters;

use DataValues\BooleanValue;
use InvalidArgumentException;
use MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanFormatter;
use PHPUnit\Framework\TestCase;
use ValueFormatters\ValueFormatter;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanFormatter
 *
 * Only depends on data-values/* and this extension's own
 * BooleanMessageLookup abstraction (via FakeBooleanMessageLookup) -- no
 * wfMessage() call happens on this path, so unlike Hooks/BooleanRdfMapper
 * this suite runs under plain `composer test`, same as BooleanParserTest.
 *
 * Uses BooleanFormatter::FORMAT_PLAIN/FORMAT_WIKI/FORMAT_HTML/etc.
 * throughout, not ValueFormatters\ValueFormatter's constants -- that
 * interface declares no format constants (confirmed against the installed
 * data-values/interfaces 1.2.0 source; it declares only OPT_LANG). An
 * earlier version of this file assumed otherwise and failed at runtime
 * with "Undefined constant" errors -- see BooleanFormatter's own docblock
 * for the full correction.
 *
 * @license GPL-2.0-or-later
 */
class BooleanFormatterTest extends TestCase {

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

		$this->assertSame( $expectedText, $formatter->format( new BooleanValue( $rawValue ) ) );
	}

	public function plainAndWikiFormatProvider(): array {
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

		$this->assertSame( $expectedText, $formatter->format( new BooleanValue( $rawValue ) ) );
	}

	public function glyphFormatProvider(): array {
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
		$this->assertSame( 'True', $formatter->format( new BooleanValue( true ) ) );
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

		$result = $formatter->format( new BooleanValue( true ) );

		$this->assertStringNotContainsString( '<b>', $result );
		$this->assertStringContainsString( '&lt;b&gt;', $result );
		$this->assertStringContainsString( '&quot;', $result );
		$this->assertStringContainsString( '&amp;', $result );
	}

	public function htmlEscapedFormatProvider(): array {
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

		$this->assertSame( 'A & B', $formatter->format( new BooleanValue( true ) ) );
	}

	public function testRejectsNonBooleanValueInput(): void {
		$formatter = new BooleanFormatter( BooleanFormatter::FORMAT_PLAIN, new FakeBooleanMessageLookup() );

		$this->expectException( InvalidArgumentException::class );

		$formatter->format( 'not a BooleanValue' );
	}

}