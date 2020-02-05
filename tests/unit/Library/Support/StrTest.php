<?php
declare(strict_types=1);

namespace Twist\Test\Library\Support;

use PHPUnit\Framework\TestCase;
use Twist\Library\Support\Str;

/**
 * Class StrTest
 *
 * @package Twist\Test\Library\Support
 */
final class StrTest extends TestCase
{

	public function testStringAscii(): void
	{
		$this->assertSame('@', Str::ascii('@'));
		$this->assertSame('u', Str::ascii('ü'));
	}

	public function testStringAsciiWithSpecificLocale(): void
	{
		$this->assertSame('h H sht SHT a A y Y', Str::ascii('х Х щ Щ ъ Ъ ь Ь', 'bg'));
		$this->assertSame('ae oe ue AE OE UE', Str::ascii('ä ö ü Ä Ö Ü', 'de'));
	}
}
