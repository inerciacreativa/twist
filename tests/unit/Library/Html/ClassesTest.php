<?php
declare(strict_types=1);

namespace Twist\Test\Library\Html;

use Twist\Library\Html\Classes;
use PHPUnit\Framework\TestCase;

/**
 * Class ClassesTest
 *
 * @package Tests\Unit
 */
final class ClassesTest extends TestCase
{

    public function testAdd(): void
    {
        $classes = Classes::make('test');
        $classes->add(['is-true', 'is-true']);
        $this->assertEquals(['test', 'is-true'], $classes->all());

        $classes->add('default');
        $this->assertEquals(['test', 'is-true', 'default'], $classes->all());

		$classes->add([1, '-0test', ['_test']]);
		$this->assertEquals(['test', 'is-true', 'default', '_test'], $classes->all());

		$classes->add(Classes::make(['is-ok']));
		$this->assertEquals(['test', 'is-true', 'default', '_test', 'is-ok'], $classes->all());
    }

    public function testRemove(): void
    {
        $classes = Classes::make(['is-true', 'is-false', 'is-delete']);
        $classes->remove(['is-false']);
        $this->assertEquals(['is-true', 'is-delete'], $classes->all());

        $classes->add(['is-default', 'to-delete']);
        $classes->remove(['is-delete', 'to-delete']);
        $this->assertEquals(['is-true', 'is-default'], $classes->all());
    }

	public function testHas(): void
	{
		$classes = Classes::make(['is-true', 'is-false', 'is-delete']);
		$this->assertTrue($classes->has('is-true'));

		$classes = Classes::make(['is-true', 'is-false', 'is-delete']);
		$this->assertFalse($classes->has('test'));
	}

    public function testOnly(): void
    {
        $classes = Classes::make(['is-true', 'is-false', 'is-delete']);
        $this->assertEquals(['is-true'], $classes->only(['is-true'])->all());

        $classes = Classes::make(['is-true', 'is-false', 'is-delete']);
        $this->assertEquals(['is-true', 'is-false'], $classes->only(['is-true', 'is-false'])->all());

        $classes = Classes::make(['is-true', 'is-false', 'is-delete']);
        $this->assertEquals(['is-true'], $classes->only(['is-true', 'is-default'])->all());
    }

    public function testReplace(): void
	{
		$classes = Classes::make(['is-true', 'is-false', 'is-delete']);
		$classes->replace('is-true', 'is-test');
		$this->assertEquals(['is-test', 'is-false', 'is-delete'], $classes->all());

		$classes->replace(['is-test', 'is-delete'], ['test', 'delete']);
		$this->assertEquals(['test', 'is-false', 'delete'], $classes->all());
	}
}
