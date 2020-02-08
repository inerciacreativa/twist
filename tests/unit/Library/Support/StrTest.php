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

	public function testAscii(): void
	{
		$this->assertSame('@', Str::ascii('@'));
		$this->assertSame('u', Str::ascii('ü'));
	}

	public function testAsciiWithSpecificLocale(): void
	{
		$this->assertSame('h H sht SHT a A y Y', Str::ascii('х Х щ Щ ъ Ъ ь Ь', 'bg'));
		$this->assertSame('ae oe ue AE OE UE', Str::ascii('ä ö ü Ä Ö Ü', 'de'));
	}

	public function testToEntities(): void
	{
		$this->assertSame('Jos&eacute;', Str::toEntities('José'));
		$this->assertSame('<span>Espa&ntilde;a</span>', Str::toEntities('<span>España</span>'));
	}

	public function testFromEntities(): void
	{
		$this->assertSame('José', Str::fromEntities('Jos&eacute;'));
		$this->assertSame('<span>España</span>', Str::fromEntities('<span>Espa&ntilde;a</span>'));
	}

	public function testStripTags(): void
	{
		$this->assertSame('Twist is a free, open source WordPress theme.', Str::stripTags('<strong>Twist</strong> is a free, open source WordPress theme.'));
		$this->assertSame('Twist is a free, open source WordPress theme.', Str::stripTags('<script>console.log("Twist");</script><strong>Twist</strong> is a free, open source WordPress theme.'));
		$this->assertSame('Twist is a free, open source WordPress theme. WordPress', Str::stripTags('<strong>Twist</strong> is a free, open source WordPress theme. <figure><img src="foo.jpg"><figcaption>WordPress</figcaption></figure>'));
		$this->assertSame('Twist is a free, open source WordPress theme.', Str::stripTags('<strong>Twist</strong> is a free, open source WordPress theme. <figure><img src="foo.jpg"><figcaption>WordPress</figcaption></figure>', ['figure']));
	}

	public function testLength(): void
	{
		$this->assertEquals(11, Str::length('foo bar baz'));
		$this->assertEquals(11, Str::length('foo bar baz', 'UTF-8'));
	}

	public function testStrContains(): void
	{
		$this->assertTrue(Str::contains('WordPress', 'ord'));
		$this->assertTrue(Str::contains('WordPress', 'WordPress'));
		$this->assertTrue(Str::contains('WordPress', ['ess']));
		$this->assertTrue(Str::contains('WordPress', ['xxx', 'Press']));
		$this->assertFalse(Str::contains('WordPress', 'xxx'));
		$this->assertFalse(Str::contains('WordPress', ['xxx']));
		$this->assertFalse(Str::contains('WordPress', ''));
	}

	public function testStartsWith(): void
	{
		$this->assertTrue(Str::startsWith('jason', 'jas'));
		$this->assertTrue(Str::startsWith('jason', 'jason'));
		$this->assertTrue(Str::startsWith('jason', ['jas']));
		$this->assertTrue(Str::startsWith('jason', ['day', 'jas']));
		$this->assertFalse(Str::startsWith('jason', 'day'));
		$this->assertFalse(Str::startsWith('jason', ['day']));
		$this->assertFalse(Str::startsWith('jason', ''));
		$this->assertFalse(Str::startsWith('7', ' 7'));
		$this->assertTrue(Str::startsWith('7a', '7'));
		$this->assertTrue(Str::startsWith('7a', 7));
		$this->assertTrue(Str::startsWith('7.12a', 7.12));
		$this->assertFalse(Str::startsWith('7.12a', 7.13));
		$this->assertTrue(Str::startsWith('7.123', '7'));
		$this->assertTrue(Str::startsWith('7.123', '7.12'));
		$this->assertFalse(Str::startsWith('7.123', '7.13'));
		// Test for multibyte string support
		$this->assertTrue(Str::startsWith('Jönköping', 'Jö'));
		$this->assertTrue(Str::startsWith('Malmö', 'Malmö'));
		$this->assertFalse(Str::startsWith('Jönköping', 'Jonko'));
		$this->assertFalse(Str::startsWith('Malmö', 'Malmo'));
	}

	public function testEndsWith(): void
	{
		$this->assertTrue(Str::endsWith('jason', 'on'));
		$this->assertTrue(Str::endsWith('jason', 'jason'));
		$this->assertTrue(Str::endsWith('jason', ['on']));
		$this->assertTrue(Str::endsWith('jason', ['no', 'on']));
		$this->assertFalse(Str::endsWith('jason', 'no'));
		$this->assertFalse(Str::endsWith('jason', ['no']));
		$this->assertFalse(Str::endsWith('jason', ''));
		$this->assertFalse(Str::endsWith('7', ' 7'));
		$this->assertTrue(Str::endsWith('a7', '7'));
		$this->assertTrue(Str::endsWith('a7', 7));
		$this->assertTrue(Str::endsWith('a7.12', 7.12));
		$this->assertFalse(Str::endsWith('a7.12', 7.13));
		$this->assertTrue(Str::endsWith('0.27', '7'));
		$this->assertTrue(Str::endsWith('0.27', '0.27'));
		$this->assertFalse(Str::endsWith('0.27', '8'));
		// Test for multibyte string support
		$this->assertTrue(Str::endsWith('Jönköping', 'öping'));
		$this->assertTrue(Str::endsWith('Malmö', 'mö'));
		$this->assertFalse(Str::endsWith('Jönköping', 'oping'));
		$this->assertFalse(Str::endsWith('Malmö', 'mo'));
	}

	public function testSubstring(): void
	{
		$this->assertSame('Ё', Str::substring('БГДЖИЛЁ', -1));
		$this->assertSame('ЛЁ', Str::substring('БГДЖИЛЁ', -2));
		$this->assertSame('И', Str::substring('БГДЖИЛЁ', -3, 1));
		$this->assertSame('ДЖИЛ', Str::substring('БГДЖИЛЁ', 2, -1));
		$this->assertEmpty(Str::substring('БГДЖИЛЁ', 4, -4));
		$this->assertSame('ИЛ', Str::substring('БГДЖИЛЁ', -3, -1));
		$this->assertSame('ГДЖИЛЁ', Str::substring('БГДЖИЛЁ', 1));
		$this->assertSame('ГДЖ', Str::substring('БГДЖИЛЁ', 1, 3));
		$this->assertSame('БГДЖ', Str::substring('БГДЖИЛЁ', 0, 4));
		$this->assertSame('Ё', Str::substring('БГДЖИЛЁ', -1, 1));
		$this->assertEmpty(Str::substring('Б', 2));
	}

	public function testReplaceFirst(): void
	{
		$this->assertSame('fooqux foobar', Str::replaceFirst('foobar foobar', 'bar', 'qux'));
		$this->assertSame('foo/qux? foo/bar?', Str::replaceFirst('foo/bar? foo/bar?', 'bar?', 'qux?'));
		$this->assertSame('foo foobar', Str::replaceFirst('foobar foobar', 'bar', ''));
		$this->assertSame('foobar foobar', Str::replaceFirst('foobar foobar', 'xxx', 'yyy'));
		$this->assertSame('foobar foobar', Str::replaceFirst('foobar foobar', '', 'yyy'));
		// Test for multibyte string support
		$this->assertSame('Jxxxnköping Malmö', Str::replaceFirst('Jönköping Malmö', 'ö', 'xxx'));
		$this->assertSame('Jönköping Malmö', Str::replaceFirst('Jönköping Malmö', '', 'yyy'));
	}

	public function testReplaceLast(): void
	{
		$this->assertSame('foobar fooqux', Str::replaceLast('foobar foobar', 'bar', 'qux'));
		$this->assertSame('foo/bar? foo/qux?', Str::replaceLast('foo/bar? foo/bar?', 'bar?', 'qux?'));
		$this->assertSame('foobar foo', Str::replaceLast('foobar foobar', 'bar', ''));
		$this->assertSame('foobar foobar', Str::replaceLast('foobar foobar', 'xxx', 'yyy'));
		$this->assertSame('foobar foobar', Str::replaceLast('foobar foobar', '', 'yyy'));
		// Test for multibyte string support
		$this->assertSame('Malmö Jönkxxxping', Str::replaceLast('Malmö Jönköping', 'ö', 'xxx'));
		$this->assertSame('Malmö Jönköping', Str::replaceLast('Malmö Jönköping', '', 'yyy'));
	}

	public function testUpper(): void
	{
		$this->assertSame('FOO BAR BAZ', Str::upper('foo bar baz'));
		$this->assertSame('FOO BAR BAZ', Str::upper('foO bAr BaZ'));
	}

	public function testLower(): void
	{
		$this->assertSame('foo bar baz', Str::lower('FOO BAR BAZ'));
		$this->assertSame('foo bar baz', Str::lower('fOo Bar bAz'));
	}

	public function testSnake(): void
	{
		$this->assertSame('twist_p_h_p_framework', Str::snake('TwistPHPFramework'));
		$this->assertSame('twist_php_framework', Str::snake('TwistPhpFramework'));
		$this->assertSame('twist php framework', Str::snake('TwistPhpFramework', ' '));
		$this->assertSame('twist_php_framework', Str::snake('Twist Php Framework'));
		$this->assertSame('twist_php_framework', Str::snake('Twist    Php      Framework   '));
		// ensure cache keys don't overlap
		$this->assertSame('twist__php__framework', Str::snake('TwistPhpFramework', '__'));
		$this->assertSame('twist_php_framework_', Str::snake('TwistPhpFramework_', '_'));
		$this->assertSame('twist_php_framework', Str::snake('twist php Framework'));
		$this->assertSame('twist_php_frame_work', Str::snake('twist php FrameWork'));
		// prevent breaking changes
		$this->assertSame('foo-bar', Str::snake('foo-bar'));
		$this->assertSame('foo-_bar', Str::snake('Foo-Bar'));
		$this->assertSame('foo__bar', Str::snake('Foo_Bar'));
		$this->assertSame('żółtałódka', Str::snake('ŻółtaŁódka'));
	}

	public function testCamel(): void
	{
		$this->assertSame('twistPHPFramework', Str::camel('Twist_p_h_p_framework'));
		$this->assertSame('twistPhpFramework', Str::camel('Twist_php_framework'));
		$this->assertSame('twistPhPFramework', Str::camel('Twist-phP-framework'));
		$this->assertSame('twistPhpFramework', Str::camel('Twist  -_-  php   -_-   framework   '));

		$this->assertSame('fooBar', Str::camel('FooBar'));
		$this->assertSame('fooBar', Str::camel('foo_bar'));
		$this->assertSame('fooBar', Str::camel('foo_bar')); // test cache
		$this->assertSame('fooBarBaz', Str::camel('Foo-barBaz'));
		$this->assertSame('fooBarBaz', Str::camel('foo-bar_baz'));
	}

	public function testStudly(): void
	{
		$this->assertSame('TwistPHPFramework', Str::studly('twist_p_h_p_framework'));
		$this->assertSame('TwistPhpFramework', Str::studly('twist_php_framework'));
		$this->assertSame('TwistPhPFramework', Str::studly('twist-phP-framework'));
		$this->assertSame('TwistPhpFramework', Str::studly('twist  -_-  php   -_-   framework   '));

		$this->assertSame('FooBar', Str::studly('fooBar'));
		$this->assertSame('FooBar', Str::studly('foo_bar'));
		$this->assertSame('FooBar', Str::studly('foo_bar')); // test cache
		$this->assertSame('FooBarBaz', Str::studly('foo-barBaz'));
		$this->assertSame('FooBarBaz', Str::studly('foo-bar_baz'));
	}

	public function testFinish(): void
	{
		$this->assertSame('abbc', Str::finish('ab', 'bc'));
		$this->assertSame('abbc', Str::finish('abbcbc', 'bc'));
		$this->assertSame('abcbbc', Str::finish('abcbbcbc', 'bc'));
	}

	public function testWhitespace(): void
	{
		$this->assertSame('hello world', Str::whitespace('hello world'));
		$this->assertSame('hello world', Str::whitespace('hello    world'));
		$this->assertSame('hello world', Str::whitespace('hello		world'));
		$this->assertSame('hello world', Str::whitespace('hello
		world'));
		$this->assertSame('hello world', Str::whitespace(" hello\n\r\tworld"));
		$this->assertSame('hello world', Str::whitespace('hello&nbsp;&nbsp;world'));
	}

	public function testLimitedByChars(): void
	{
		$this->assertSame('Twist is…', Str::chars('Twist is a free, open source WordPress theme.', 8));
		$this->assertSame('这是一…', Str::chars('这是一段中文', 6));

		$string = 'The WordPress theme for web artisans.';
		$this->assertSame('The Word…', Str::chars($string, 8));
		$this->assertSame('The WordPress', Str::chars($string, 13, ''));
		$this->assertSame('The WordPress theme for web artisans.', Str::chars($string, 100));

		$nonAsciiString = '这是一段中文';
		$this->assertSame('这是一…', Str::chars($nonAsciiString, 6));
		$this->assertSame('这是一', Str::chars($nonAsciiString, 6, ''));
	}

	public function testLimitedByWords(): void
	{
		$this->assertSame('Jose…', Str::words('Jose Cuesta', 1));
		$this->assertSame('Jose___', Str::words('Jose Cuesta', 1, '___'));
		$this->assertSame('Jose Cuesta', Str::words('Jose Cuesta', 3));
		$this->assertSame('Twist is a free.', Str::words('Twist is a free, open source WordPress theme.', 4, '.'));
	}

	public function testSlug(): void
	{
		$this->assertSame('hello-world', Str::slug('hello world'));
		$this->assertSame('hello-world', Str::slug('hello-world'));
		$this->assertSame('hello-world', Str::slug('hello_world'));
		$this->assertSame('hello_world', Str::slug('hello_world', '_'));
		$this->assertSame('user-at-host', Str::slug('user@host'));
		$this->assertSame('سلام-دنیا', Str::slug('سلام دنیا', '-', null));
	}

}
