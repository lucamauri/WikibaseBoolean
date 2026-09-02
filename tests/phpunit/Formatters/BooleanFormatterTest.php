<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\WikibaseBoolean\Tests\Formatters;

use MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanFormatter;
use PHPUnit\Framework\TestCase;
use ValueFormatters\ValueFormatter;

/**
 * @covers \MediaWiki\Extension\WikibaseBoolean\Formatters\BooleanFormatter
 *
 * Skeleton only -- see BooleanParserTest's docblock for the rationale.
 * Real cases (plain/HTML/wikitext output for true and for false) belong
 * here once BooleanFormatter::format() is implemented.
 *
 * @license GPL-2.0-or-later
 */
class BooleanFormatterTest extends TestCase {

	public function testImplementsValueFormatter(): void {
		$formatter = new BooleanFormatter( ValueFormatter::FORMAT_PLAIN );

		$this->assertInstanceOf( ValueFormatter::class, $formatter );
	}

}
