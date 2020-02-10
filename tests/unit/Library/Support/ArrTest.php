<?php
declare(strict_types=1);

namespace Twist\Test\Library\Support;

use ArrayObject;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Twist\Library\Data\Collection;
use Twist\Library\Support\Arr;

/**
 * Class ArrTest
 *
 * @package Twist\Test\Library\Support
 */
final class ArrTest extends TestCase
{

	public function testDot(): void
	{
		$array = Arr::dot(['foo' => ['bar' => 'baz']]);
		$this->assertEquals(['foo.bar' => 'baz'], $array);
		$array = Arr::dot([]);
		$this->assertEquals([], $array);
		$array = Arr::dot(['foo' => []]);
		$this->assertEquals(['foo' => []], $array);
		$array = Arr::dot(['foo' => ['bar' => []]]);
		$this->assertEquals(['foo.bar' => []], $array);
		$array = Arr::dot(['name' => 'taylor', 'languages' => ['php' => true]]);
		$this->assertEquals($array, [
			'name'          => 'taylor',
			'languages.php' => true,
		]);
	}

	public function testGet(): void
	{
		$array = ['products.desk' => ['price' => 100]];
		$this->assertEquals(['price' => 100], Arr::get($array, 'products.desk'));

		$array = ['products' => ['desk' => ['price' => 100]]];
		$value = Arr::get($array, 'products.desk');
		$this->assertEquals(['price' => 100], $value);

		// Test null array values
		$array = ['foo' => null, 'bar' => ['baz' => null]];
		$this->assertNull(Arr::get($array, 'foo', 'default'));
		$this->assertNull(Arr::get($array, 'bar.baz', 'default'));

		// Test direct ArrayAccess object
		$array             = ['products' => ['desk' => ['price' => 100]]];
		$arrayAccessObject = new ArrayObject($array);
		$value             = Arr::get($arrayAccessObject, 'products.desk');
		$this->assertEquals(['price' => 100], $value);

		// Test array containing ArrayAccess object
		$arrayAccessChild = new ArrayObject(['products' => ['desk' => ['price' => 100]]]);
		$array            = ['child' => $arrayAccessChild];
		$value            = Arr::get($array, 'child.products.desk');
		$this->assertEquals(['price' => 100], $value);

		// Test array containing multiple nested ArrayAccess objects
		$arrayAccessChild  = new ArrayObject(['products' => ['desk' => ['price' => 100]]]);
		$arrayAccessParent = new ArrayObject(['child' => $arrayAccessChild]);
		$array             = ['parent' => $arrayAccessParent];
		$value             = Arr::get($array, 'parent.child.products.desk');
		$this->assertEquals(['price' => 100], $value);

		// Test missing ArrayAccess object field
		$arrayAccessChild  = new ArrayObject(['products' => ['desk' => ['price' => 100]]]);
		$arrayAccessParent = new ArrayObject(['child' => $arrayAccessChild]);
		$array             = ['parent' => $arrayAccessParent];
		$value             = Arr::get($array, 'parent.child.desk');
		$this->assertNull($value);

		// Test missing ArrayAccess object field
		$arrayAccessObject = new ArrayObject(['products' => ['desk' => null]]);
		$array             = ['parent' => $arrayAccessObject];
		$value             = Arr::get($array, 'parent.products.desk.price');
		$this->assertNull($value);

		// Test null ArrayAccess object fields
		$array = new ArrayObject([
			'foo' => null,
			'bar' => new ArrayObject(['baz' => null]),
		]);
		$this->assertNull(Arr::get($array, 'foo', 'default'));
		$this->assertNull(Arr::get($array, 'bar.baz', 'default'));

		// Test null key returns the whole array
		$array = ['foo', 'bar'];
		$this->assertEquals($array, Arr::get($array, null));

		// Test $array not an array
		$this->assertSame('default', Arr::get(null, 'foo', 'default'));
		$this->assertSame('default', Arr::get(false, 'foo', 'default'));

		// Test $array not an array and key is null
		$this->assertSame('default', Arr::get(null, null, 'default'));

		// Test $array is empty and key is null
		$this->assertEmpty(Arr::get([], null));
		$this->assertEmpty(Arr::get([], null, 'default'));

		// Test numeric keys
		$array = [
			'products' => [
				['name' => 'desk'],
				['name' => 'chair'],
			],
		];
		$this->assertEquals('desk', Arr::get($array, 'products.0.name'));
		$this->assertEquals('chair', Arr::get($array, 'products.1.name'));

		// Test return default value for non-existing key.
		$array = ['names' => ['developer' => 'taylor']];
		$this->assertEquals('dayle', Arr::get($array, 'names.otherDeveloper', 'dayle'));
		$this->assertEquals('dayle', Arr::get($array, 'names.otherDeveloper', static function () {
			return 'dayle';
		}));
	}

	public function testSet(): void
	{
		$array = ['products' => ['desk' => ['price' => 100]]];
		Arr::set($array, 'products.desk.price', 200);
		$this->assertEquals(['products' => ['desk' => ['price' => 200]]], $array);
	}

	public function testAdd(): void
	{
		$array = Arr::add(['name' => 'Desk'], 'price', 100);

		$this->assertEquals(['name' => 'Desk', 'price' => 100], $array);
		$this->assertEquals(['surname' => 'Mövsümov'], Arr::add([], 'surname', 'Mövsümov'));
		$this->assertEquals(['developer' => ['name' => 'Ferid']], Arr::add([], 'developer.name', 'Ferid'));
	}

	public function testHas(): void
	{
		$array = ['products.desk' => ['price' => 100]];
		$this->assertTrue(Arr::has($array, 'products.desk'));

		$array = ['products' => ['desk' => ['price' => 100]]];
		$this->assertTrue(Arr::has($array, 'products.desk'));
		$this->assertTrue(Arr::has($array, 'products.desk.price'));
		$this->assertFalse(Arr::has($array, 'products.foo'));
		$this->assertFalse(Arr::has($array, 'products.desk.foo'));

		$array = ['foo' => null, 'bar' => ['baz' => null]];
		$this->assertTrue(Arr::has($array, 'foo'));
		$this->assertTrue(Arr::has($array, 'bar.baz'));

		$array = new ArrayObject([
			'foo' => 10,
			'bar' => new ArrayObject(['baz' => 10]),
		]);
		$this->assertTrue(Arr::has($array, 'foo'));
		$this->assertTrue(Arr::has($array, 'bar'));
		$this->assertTrue(Arr::has($array, 'bar.baz'));
		$this->assertFalse(Arr::has($array, 'xxx'));
		$this->assertFalse(Arr::has($array, 'xxx.yyy'));
		$this->assertFalse(Arr::has($array, 'foo.xxx'));
		$this->assertFalse(Arr::has($array, 'bar.xxx'));

		$array = new ArrayObject([
			'foo' => null,
			'bar' => new ArrayObject(['baz' => null]),
		]);
		$this->assertTrue(Arr::has($array, 'foo'));
		$this->assertTrue(Arr::has($array, 'bar.baz'));

		$array = ['foo', 'bar'];
		$this->assertFalse(Arr::has($array, null));
		$this->assertFalse(Arr::has(null, 'foo'));
		$this->assertFalse(Arr::has(false, 'foo'));
		$this->assertFalse(Arr::has(null, null));
		$this->assertFalse(Arr::has([], null));

		$array = ['products' => ['desk' => ['price' => 100]]];
		$this->assertTrue(Arr::has($array, ['products.desk']));
		$this->assertTrue(Arr::has($array, [
			'products.desk',
			'products.desk.price',
		]));
		$this->assertTrue(Arr::has($array, ['products', 'products']));
		$this->assertFalse(Arr::has($array, ['foo']));
		$this->assertFalse(Arr::has($array, []));
		$this->assertFalse(Arr::has($array, [
			'products.desk',
			'products.price',
		]));

		$array = [
			'products' => [
				['name' => 'desk'],
			],
		];
		$this->assertTrue(Arr::has($array, 'products.0.name'));
		$this->assertFalse(Arr::has($array, 'products.0.price'));
		$this->assertFalse(Arr::has([], [null]));
		$this->assertFalse(Arr::has(null, [null]));
		$this->assertTrue(Arr::has(['' => 'some'], ''));
		$this->assertTrue(Arr::has(['' => 'some'], ['']));
		$this->assertFalse(Arr::has([''], ''));
		$this->assertFalse(Arr::has([], ''));
		$this->assertFalse(Arr::has([], ['']));
	}

	public function testForget(): void
	{
		$array = ['products' => ['desk' => ['price' => 100]]];
		Arr::forget($array, null);
		$this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

		$array = ['products' => ['desk' => ['price' => 100]]];
		Arr::forget($array, []);
		$this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

		$array = ['products' => ['desk' => ['price' => 100]]];
		Arr::forget($array, 'products.desk');
		$this->assertEquals(['products' => []], $array);

		$array = ['products' => ['desk' => ['price' => 100]]];
		Arr::forget($array, 'products.desk.price');
		$this->assertEquals(['products' => ['desk' => []]], $array);

		$array = ['products' => ['desk' => ['price' => 100]]];
		Arr::forget($array, 'products.final.price');
		$this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

		$array = ['shop' => ['cart' => [150 => 0]]];
		Arr::forget($array, 'shop.final.cart');
		$this->assertEquals(['shop' => ['cart' => [150 => 0]]], $array);

		$array = [
			'products' => [
				'desk' => [
					'price' => [
						'original' => 50,
						'taxes'    => 60,
					],
				],
			],
		];
		Arr::forget($array, 'products.desk.price.taxes');
		$this->assertEquals(['products' => ['desk' => ['price' => ['original' => 50]]]], $array);

		$array = [
			'products' => [
				'desk' => [
					'price' => [
						'original' => 50,
						'taxes'    => 60,
					],
				],
			],
		];
		Arr::forget($array, 'products.desk.final.taxes');
		$this->assertEquals([
			'products' => [
				'desk' => [
					'price' => [
						'original' => 50,
						'taxes'    => 60,
					],
				],
			],
		], $array);

		$array = [
			'products' => [
				'desk' => ['price' => 50],
				null   => 'something',
			],
		];
		Arr::forget($array, ['products.amount.all', 'products.desk.price']);
		$this->assertEquals([
			'products' => [
				'desk' => [],
				null   => 'something',
			],
		], $array);

		// Only works on first level keys
		$array = ['joe@example.com' => 'Joe', 'jane@example.com' => 'Jane'];
		Arr::forget($array, 'joe@example.com');
		$this->assertEquals(['jane@example.com' => 'Jane'], $array);

		// Does not work for nested keys
		$array = [
			'emails' => [
				'joe@example.com' => ['name' => 'Joe'],
				'jane@localhost'  => ['name' => 'Jane'],
			],
		];
		Arr::forget($array, [
			'emails.joe@example.com',
			'emails.jane@localhost',
		]);
		$this->assertEquals(['emails' => ['joe@example.com' => ['name' => 'Joe']]], $array);
	}

	public function testExcept(): void
	{
		$array = ['name' => 'Jose', 'age' => 45];
		$this->assertEquals(['age' => 45], Arr::except($array, ['name']));
		$this->assertEquals(['age' => 45], Arr::except($array, 'name'));

		$array = [
			'name'      => 'Jose',
			'framework' => ['language' => 'PHP', 'name' => 'Twist'],
		];
		$this->assertEquals(['name' => 'Jose'], Arr::except($array, 'framework'));
		$this->assertEquals([
			'name'      => 'Jose',
			'framework' => ['name' => 'Twist'],
		], Arr::except($array, 'framework.language'));
		$this->assertEquals(['framework' => ['language' => 'PHP']], Arr::except($array, [
			'name',
			'framework.name',
		]));
	}

	public function testOnly(): void
	{
		$array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
		$array = Arr::only($array, ['name', 'price']);
		$this->assertEquals(['name' => 'Desk', 'price' => 100], $array);
		$this->assertEmpty(Arr::only($array, ['nonExistingKey']));
	}

	public function testExists(): void
	{
		$this->assertTrue(Arr::exists([1], 0));
		$this->assertTrue(Arr::exists([null], 0));
		$this->assertTrue(Arr::exists(['a' => 1], 'a'));
		$this->assertTrue(Arr::exists(['a' => null], 'a'));
		$this->assertTrue(Arr::exists(new Collection(['a' => null]), 'a'));
		$this->assertFalse(Arr::exists([1], 1));
		$this->assertFalse(Arr::exists([null], 1));
		$this->assertFalse(Arr::exists(['a' => 1], 0));
		$this->assertFalse(Arr::exists(new Collection(['a' => null]), 'b'));
	}

	public function testFirst(): void
	{
		$array = [100, 200, 300];

		$value = Arr::first($array, static function ($value) {
			return $value >= 150;
		});

		$this->assertEquals(200, $value);
		$this->assertEquals(100, Arr::first($array));
	}

	public function testLast(): void
	{
		$array = [100, 200, 300];

		$last = Arr::last($array, static function ($value) {
			return $value < 250;
		});
		$this->assertEquals(200, $last);

		$last = Arr::last($array, static function ($value, $key) {
			return $key < 2;
		});
		$this->assertEquals(200, $last);
		$this->assertEquals(300, Arr::last($array));
	}

	public function testDivide(): void
	{
		[$keys, $values] = Arr::divide(['name' => 'Desk']);
		$this->assertEquals(['name'], $keys);
		$this->assertEquals(['Desk'], $values);
	}

	public function testCollapse(): void
	{
		$data = [['foo', 'bar'], ['baz']];
		$this->assertEquals(['foo', 'bar', 'baz'], Arr::collapse($data));

		$array = [
			[1],
			[2],
			[3],
			['foo', 'bar'],
			new Collection(['baz', 'boom']),
		];
		$this->assertEquals([
			1,
			2,
			3,
			'foo',
			'bar',
			'baz',
			'boom',
		], Arr::collapse($array));
	}

	public function testFlatten(): void
	{
		// Flat arrays are unaffected
		$array = ['#foo', '#bar', '#baz'];
		$this->assertEquals(['#foo', '#bar', '#baz'], Arr::flatten($array));

		// Nested arrays are flattened with existing flat items
		$array = [['#foo', '#bar'], '#baz'];
		$this->assertEquals(['#foo', '#bar', '#baz'], Arr::flatten($array));

		// Flattened array includes "null" items
		$array = [['#foo', null], '#baz', null];
		$this->assertEquals(['#foo', null, '#baz', null], Arr::flatten($array));

		// Sets of nested arrays are flattened
		$array = [['#foo', '#bar'], ['#baz']];
		$this->assertEquals(['#foo', '#bar', '#baz'], Arr::flatten($array));

		// Deeply nested arrays are flattened
		$array = [['#foo', ['#bar']], ['#baz']];
		$this->assertEquals(['#foo', '#bar', '#baz'], Arr::flatten($array));

		// Nested arrays are flattened alongside arrays
		$array = [new Collection(['#foo', '#bar']), ['#baz']];
		$this->assertEquals(['#foo', '#bar', '#baz'], Arr::flatten($array));

		// Nested arrays containing plain arrays are flattened
		$array = [new Collection(['#foo', ['#bar']]), ['#baz']];
		$this->assertEquals(['#foo', '#bar', '#baz'], Arr::flatten($array));

		// Nested arrays containing arrays are flattened
		$array = [['#foo', new Collection(['#bar'])], ['#baz']];
		$this->assertEquals(['#foo', '#bar', '#baz'], Arr::flatten($array));

		// Nested arrays containing arrays containing arrays are flattened
		$array = [['#foo', new Collection(['#bar', ['#zap']])], ['#baz']];
		$this->assertEquals([
			'#foo',
			'#bar',
			'#zap',
			'#baz',
		], Arr::flatten($array));
	}

	public function testPluck(): void
	{
		$data = [
			'post-1' => [
				'comments' => [
					'tags' => [
						'#foo',
						'#bar',
					],
				],
			],
			'post-2' => [
				'comments' => [
					'tags' => [
						'#baz',
					],
				],
			],
		];

		$this->assertEquals([
			0 => [
				'tags' => [
					'#foo',
					'#bar',
				],
			],
			1 => [
				'tags' => [
					'#baz',
				],
			],
		], Arr::pluck($data, 'comments'));

		$this->assertEquals([
			['#foo', '#bar'],
			['#baz'],
		], Arr::pluck($data, 'comments.tags'));
		$this->assertEquals([null, null], Arr::pluck($data, 'foo'));
		$this->assertEquals([null, null], Arr::pluck($data, 'foo.bar'));

		$array = [
			['developer' => ['name' => 'Jose']],
			['developer' => ['name' => 'Daniel']],
		];
		$array = Arr::pluck($array, 'developer.name');
		$this->assertEquals(['Jose', 'Daniel'], $array);
	}

	public function testPluckWithArrayValue(): void
	{
		$array = [
			['developer' => ['name' => 'Jose']],
			['developer' => ['name' => 'Daniel']],
		];
		$array = Arr::pluck($array, ['developer', 'name']);
		$this->assertEquals(['Jose', 'Daniel'], $array);
	}

	public function testPluckWithKeys(): void
	{
		$array = [
			['name' => 'Taylor', 'role' => 'developer'],
			['name' => 'Abigail', 'role' => 'developer'],
		];

		$test1 = Arr::pluck($array, 'role', 'name');
		$test2 = Arr::pluck($array, null, 'name');

		$this->assertEquals([
			'Taylor'  => 'developer',
			'Abigail' => 'developer',
		], $test1);

		$this->assertEquals([
			'Taylor'  => ['name' => 'Taylor', 'role' => 'developer'],
			'Abigail' => ['name' => 'Abigail', 'role' => 'developer'],
		], $test2);
	}

	public function testArrayPluckWithArrayAndObjectValues(): void
	{
		$array = [
			(object) ['name' => 'taylor', 'email' => 'foo'],
			['name' => 'dayle', 'email' => 'bar'],
		];
		$this->assertEquals(['taylor', 'dayle'], Arr::pluck($array, 'name'));
		$this->assertEquals([
			'taylor' => 'foo',
			'dayle'  => 'bar',
		], Arr::pluck($array, 'email', 'name'));
	}

	public function testArrayPluckWithNestedKeys(): void
	{
		$array = [
			['user' => ['taylor', 'otwell']],
			['user' => ['dayle', 'rees']],
		];
		$this->assertEquals(['taylor', 'dayle'], Arr::pluck($array, 'user.0'));
		$this->assertEquals(['taylor', 'dayle'], Arr::pluck($array, [
			'user',
			0,
		]));
		$this->assertEquals([
			'taylor' => 'otwell',
			'dayle'  => 'rees',
		], Arr::pluck($array, 'user.1', 'user.0'));
		$this->assertEquals([
			'taylor' => 'otwell',
			'dayle'  => 'rees',
		], Arr::pluck($array, ['user', 1], ['user', 0]));
	}

	public function testArrayPluckWithNestedArrays(): void
	{
		$array = [
			[
				'account' => 'a',
				'users'   => [
					[
						'first' => 'taylor',
						'last'  => 'otwell',
						'email' => 'taylorotwell@gmail.com',
					],
				],
			],
			[
				'account' => 'b',
				'users'   => [
					['first' => 'abigail', 'last' => 'otwell'],
					['first' => 'dayle', 'last' => 'rees'],
				],
			],
		];

		$this->assertEquals([
			['taylor'],
			['abigail', 'dayle'],
		], Arr::pluck($array, 'users.*.first'));
		$this->assertEquals([
			'a' => ['taylor'],
			'b' => ['abigail', 'dayle'],
		], Arr::pluck($array, 'users.*.first', 'account'));
		$this->assertEquals([
			['taylorotwell@gmail.com'],
			[null, null],
		], Arr::pluck($array, 'users.*.email'));
	}

	public function testPrepend(): void
	{
		$array = Arr::prepend(['one', 'two', 'three', 'four'], 'zero');
		$this->assertEquals(['zero', 'one', 'two', 'three', 'four'], $array);
		$array = Arr::prepend(['one' => 1, 'two' => 2], 0, 'zero');
		$this->assertEquals(['zero' => 0, 'one' => 1, 'two' => 2], $array);
	}

	public function testPush(): void
	{
		$array = ['one', 'two', 'three'];
		$this->assertEquals([
			'one',
			'two',
			'three',
			'four',
		], Arr::push($array, 'four'));
		$this->assertEquals([
			'one',
			'two',
			'three',
			'four',
			'six',
			'seven',
			'eight',
		], Arr::push($array, 'six', 'seven', 'eight'));
	}

	public function testPull(): void
	{
		$array = ['name' => 'Desk', 'price' => 100];
		$name  = Arr::pull($array, 'name');
		$this->assertEquals('Desk', $name);
		$this->assertEquals(['price' => 100], $array);

		// Only works on first level keys
		$array = ['joe@example.com' => 'Joe', 'jane@localhost' => 'Jane'];
		$name  = Arr::pull($array, 'joe@example.com');
		$this->assertEquals('Joe', $name);
		$this->assertEquals(['jane@localhost' => 'Jane'], $array);

		// Does not work for nested keys
		$array = [
			'emails' => [
				'joe@example.com' => 'Joe',
				'jane@localhost'  => 'Jane',
			],
		];
		$name  = Arr::pull($array, 'emails.joe@example.com');
		$this->assertNull($name);
		$this->assertEquals([
			'emails' => [
				'joe@example.com' => 'Joe',
				'jane@localhost'  => 'Jane',
			],
		], $array);
	}

	public function testSort(): void
	{
		$unsorted = [
			['name' => 'Desk'],
			['name' => 'Chair'],
		];

		$expected = [
			['name' => 'Chair'],
			['name' => 'Desk'],
		];

		$sorted = array_values(Arr::sort($unsorted));
		$this->assertEquals($expected, $sorted);

		// sort with closure
		$sortedWithClosure = array_values(Arr::sort($unsorted, static function ($value) {
			return $value['name'];
		}));
		$this->assertEquals($expected, $sortedWithClosure);

		// sort with dot notation
		$sortedWithDotNotation = array_values(Arr::sort($unsorted, 'name'));
		$this->assertEquals($expected, $sortedWithDotNotation);
	}

	public function testSortRecursive(): void
	{
		$array  = [
			'users'        => [
				[
					// should sort associative arrays by keys
					'name'    => 'joe',
					'mail'    => 'joe@example.com',
					// should sort deeply nested arrays
					'numbers' => [2, 1, 0],
				],
				[
					'name' => 'jane',
					'age'  => 25,
				],
			],
			'repositories' => [
				// should use weird `sort()` behavior on arrays of arrays
				['id' => 1],
				['id' => 0],
			],
			// should sort non-associative arrays by value
			20             => [2, 1, 0],
			30             => [
				// should sort non-incrementing numerical keys by keys
				2 => 'a',
				1 => 'b',
				0 => 'c',
			],
		];
		$expect = [
			20             => [0, 1, 2],
			30             => [
				0 => 'c',
				1 => 'b',
				2 => 'a',
			],
			'repositories' => [
				['id' => 0],
				['id' => 1],
			],
			'users'        => [
				[
					'age'  => 25,
					'name' => 'jane',
				],
				[
					'mail'    => 'joe@example.com',
					'name'    => 'joe',
					'numbers' => [0, 1, 2],
				],
			],
		];
		$this->assertEquals($expect, Arr::sortRecursive($array));
	}

	public function testWhere(): void
	{
		$array = [100, '200', 300, '400', 500];

		$array = Arr::where($array, static function ($value, $key) {
			return is_string($value);
		});
		$this->assertEquals([1 => '200', 3 => '400'], $array);

		$array = ['10' => 1, 'foo' => 3, 20 => 2];

		$array = Arr::where($array, static function ($value, $key) {
			return is_numeric($key);
		});
		$this->assertEquals(['10' => 1, 20 => 2], $array);
	}

	public function testValue(): void
	{
		$array = ['name' => 'Jose', 'role' => 'developer'];

		$this->assertEquals('Jose', Arr::value($array, 'name'));
		$this->assertNull(Arr::value($array, 'email'));
		$this->assertEquals('jose@example.com', Arr::value($array, 'email', 'jose@example.com'));
		$this->assertEquals('test', Arr::value($array, 'email', static function () {
			return 'test';
		}));
	}

	public function testValues(): void
	{
		$array = [
			static function () {
				return 'closure';
			},
			'string',
			'null' => null,
		];

		$this->assertEquals(['closure', 'string'], Arr::values($array));
	}

	public function testItems(): void
	{
		$array = ['name' => 'Jose', 'role' => 'developer'];
		$this->assertEquals($array, Arr::items($array));

		$collection = new Collection($array);
		$this->assertEquals($array, Arr::items($collection));
	}

	public function testImplode(): void
	{
		$array = ['#foo', '#bar', '#baz'];
		$this->assertEquals('#foo#bar#baz', Arr::implode($array));
		$this->assertEquals('#foo.#bar.#baz', Arr::implode($array, '.'));

		$array = [['#foo', '#bar'], '#baz'];
		$this->assertEquals('#foo#bar#baz', Arr::implode($array));

		$array = [['#foo', ['#bar']], null, ['#baz'], false];
		$this->assertEquals('#foo_#bar_#baz', Arr::implode($array, '_'));
	}

	public function testReduce(): void
	{
		$array = [1 => '#foo', 2 => '#bar', 4 => '#baz'];

		$this->assertEquals(7, Arr::reduce($array, static function ($result, $value, $key) {
			return $result + $key;
		}, 0));

		$this->assertEquals('+#foo+#bar+#baz', Arr::reduce($array, static function ($result, $value, $key) {
			return $result . '+' . $value;
		}, ''));
	}

	public function testMap(): void
	{
		$array    = [
			['developer' => ['name' => 'Jose']],
			['developer' => ['name' => 'Daniel']],
		];
		$expected = [
			['index' => 1, 'name' => 'Jose'],
			['index' => 2, 'name' => 'Daniel'],
		];

		$this->assertEquals($expected, Arr::map($array, static function ($value, $key) {
			return ['index' => ++$key, 'name' => $value['developer']['name']];
		}));
	}

	public function testDefaults(): void
	{
		$defaults = ['role' => 'developer', 'isActive' => true];

		$this->assertEquals([
			'role'     => 'developer',
			'isActive' => false,
		], Arr::defaults($defaults, ['name' => false, 'isActive' => false]));
		$this->assertEquals([
			'role'     => 'developer',
			'isActive' => false,
			'name'     => 'Jose',
		], Arr::defaults($defaults, [
			'isActive' => false,
			'name'     => 'Jose',
		], ['name']));

		$this->expectException(InvalidArgumentException::class);
		Arr::defaults($defaults, ['role' => 'tester'], ['name']);
	}

	public function testMerge(): void
	{
		$first = [
			'post-1' => [
				'comments' => [
					'tags' => [
						'#foo',
						'#bar',
					],
				],
			],
			'post-2' => [
				'comments' => [
					'tags' => [
						'#baz',
					],
				],
			],
		];

		$second = [
			'post-1' => [
				'comments' => [
					'tags' => [
						'#test',
					],
				],
			],
			'post-2' => [
				'content' => 'Lorem ipsum',
			],
		];

		$this->assertEquals([
			'post-1' => [
				'comments' => [
					'tags' => [
						'#foo',
						'#bar',
						'#test',
					],
				],
			],
			'post-2' => [
				'comments' => [
					'tags' => [
						'#baz',
					],
				],
				'content'  => 'Lorem ipsum',
			],
		], Arr::merge($first, $second));
	}

	public function testInsertBefore(): void
	{
		$array = [
			['developer' => ['name' => 'Jen']],
			['developer' => ['name' => 'Karen']],
			['developer' => ['name' => 'Matt']],
		];

		$this->assertEquals([
			['developer' => ['name' => 'Jen']],
			['developer' => ['name' => 'Karen']],
			['developer' => ['name' => 'Anonymous']],
			['developer' => ['name' => 'Matt']],
		], Arr::insertBefore($array, 2, [['developer' => ['name' => 'Anonymous']]]));

		$this->assertEquals([
			['developer' => ['name' => 'Jen']],
			['developer' => ['name' => 'Karen']],
			'Anonymous',
			['developer' => ['name' => 'Matt']],
		], Arr::insertBefore($array, 2, ['Anonymous']));
	}

	public function testInsertAfter(): void
	{
		$array = [
			['developer' => ['name' => 'Jen']],
			['developer' => ['name' => 'Karen']],
			['developer' => ['name' => 'Matt']],
		];

		$this->assertEquals([
			['developer' => ['name' => 'Jen']],
			['developer' => ['name' => 'Anonymous']],
			['developer' => ['name' => 'Karen']],
			['developer' => ['name' => 'Matt']],
		], Arr::insertAfter($array, 0, [['developer' => ['name' => 'Anonymous']]]));
	}

	public function testRemove(): void
	{
		$array = [
			'Name'     => 'Jose',
			'Mail'     => 'jose@example.com',
			'isActive' => false,
			'test'     => null,
		];

		$this->assertEquals([
			'Mail'     => 'jose@example.com',
			'isActive' => false,
			'test'     => null,
		], Arr::remove($array, 'name'));
		$this->assertEquals([
			'Name' => 'Jose',
			'Mail' => 'jose@example.com',
		], Arr::remove($array, ['isactive', 'test']));

		// Strict
		$this->assertEquals([
			'Name' => 'Jose',
			'Mail' => 'jose@example.com',
			'test' => null,
		], Arr::remove($array, 'isActive', false));
		$this->assertEquals([
			'isActive' => false,
			'test'     => null,
		], Arr::remove($array, ['Name', 'Mail']));

		// Remove null values
		$this->assertEquals([
			'Name'     => 'Jose',
			'Mail'     => 'jose@example.com',
			'isActive' => false,
		], Arr::remove($array));
	}

	public function testIsAssoc(): void
	{
		$this->assertTrue(Arr::isAssoc(['a' => 'a', 0 => 'b']));
		$this->assertTrue(Arr::isAssoc([1 => 'a', 0 => 'b']));
		$this->assertTrue(Arr::isAssoc([1 => 'a', 2 => 'b']));
		$this->assertFalse(Arr::isAssoc([0 => 'a', 1 => 'b']));
		$this->assertFalse(Arr::isAssoc(['a', 'b']));
	}

	public function testAccessible(): void
	{
		$this->assertTrue(Arr::accessible([]));
		$this->assertTrue(Arr::accessible([1, 2]));
		$this->assertTrue(Arr::accessible(['a' => 1, 'b' => 2]));
		$this->assertTrue(Arr::accessible(new Collection));

		$this->assertFalse(Arr::accessible(null));
		$this->assertFalse(Arr::accessible('abc'));
		$this->assertFalse(Arr::accessible(new stdClass));
		$this->assertFalse(Arr::accessible((object) ['a' => 1, 'b' => 2]));
	}

}
