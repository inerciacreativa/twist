<?php
declare(strict_types=1);

namespace Twist\Test\Library\Support;

use ArrayAccess;
use PHPUnit\Framework\TestCase;
use Twist\Library\Support\Data;

/**
 * Class DataTest
 *
 * @package Twist\Test\Library\Support
 */
final class DataTest extends TestCase
{

	public function testHas(): void
	{
		$object      = (object) ['users' => ['name' => ['Jose', 'Cuesta']]];
		$array       = [(object) ['users' => [(object) ['name' => 'Jose']]]];
		$dottedArray = [
			'users' => [
				'first.name'  => 'Jose',
				'middle.name' => null,
			],
		];
		$arrayAccess = new SupportTestArrayAccess([
			'price' => 56,
			'user'  => new SupportTestArrayAccess(['name' => 'Jose']),
			'email' => null,
		]);

		$this->assertTrue(Data::has($object, 'users.name.0'));
		$this->assertFalse(Data::has($object, 'users.name.2'));
		$this->assertTrue(Data::has($array, '0.users.0.name'));
		$this->assertFalse(Data::has($array, '0.users.3.name'));
		$this->assertTrue(Data::has($dottedArray, ['users', 'first.name']));
		$this->assertFalse(Data::has($dottedArray, ['users', 'last.name']));
		$this->assertTrue(Data::has($arrayAccess, ['user', 'name']));
		$this->assertTrue(Data::has($arrayAccess, ['email']));
		$this->assertFalse(Data::has($arrayAccess, 'user.email'));
	}

	public function testGet(): void
	{
		$object      = (object) ['users' => ['name' => ['Jose', 'Cuesta']]];
		$array       = [(object) ['users' => [(object) ['name' => 'Jose']]]];
		$dottedArray = [
			'users' => [
				'first.name'  => 'Jose',
				'middle.name' => null,
			],
		];
		$arrayAccess = new SupportTestArrayAccess([
			'price' => 56,
			'user'  => new SupportTestArrayAccess(['name' => 'Jose']),
			'email' => null,
		]);

		$this->assertSame('Jose', Data::get($object, 'users.name.0'));
		$this->assertSame('Jose', Data::get($array, '0.users.0.name'));
		$this->assertNull(Data::get($array, '0.users.3'));
		$this->assertSame('Not found', Data::get($array, '0.users.3', 'Not found'));
		$this->assertSame('Not found', Data::get($array, '0.users.3', static function () {
			return 'Not found';
		}));
		$this->assertSame('Jose', Data::get($dottedArray, [
			'users',
			'first.name',
		]));
		$this->assertNull(Data::get($dottedArray, ['users', 'middle.name']));
		$this->assertSame('Not found', Data::get($dottedArray, [
			'users',
			'last.name',
		], 'Not found'));
		$this->assertEquals(56, Data::get($arrayAccess, 'price'));
		$this->assertSame('Jose', Data::get($arrayAccess, 'user.name'));
		$this->assertSame('void', Data::get($arrayAccess, 'foo', 'void'));
		$this->assertSame('void', Data::get($arrayAccess, 'user.foo', 'void'));
		$this->assertNull(Data::get($arrayAccess, 'foo'));
		$this->assertNull(Data::get($arrayAccess, 'user.foo'));
		$this->assertNull(Data::get($arrayAccess, 'email', 'Not found'));
	}

	public function testGetWithNestedArrays(): void
	{
		$array = [
			['name' => 'jose', 'email' => 'josecuesta@gmail.com'],
			['name' => 'daniel'],
			['name' => 'david'],
		];

		$this->assertEquals(['jose', 'daniel', 'david'], Data::get($array, '*.name'));
		$this->assertEquals(['josecuesta@gmail.com', null, null], Data::get($array, '*.email', 'irrelevant'));

		$array = [
			'users' => [
				['first' => 'jose', 'last' => 'cuesta', 'email' => 'josecuesta@gmail.com'],
				['first' => 'daniel', 'last' => 'cuesta'],
				['first' => 'david', 'last' => 'garcía'],
			],
			'posts' => null,
		];

		$this->assertEquals(['jose', 'daniel', 'david'], Data::get($array, 'users.*.first'));
		$this->assertEquals(['josecuesta@gmail.com', null, null], Data::get($array, 'users.*.email', 'irrelevant'));
		$this->assertSame('not found', Data::get($array, 'posts.*.date', 'not found'));
		$this->assertNull(Data::get($array, 'posts.*.date'));
	}

	public function testGetWithDoubleNestedArraysCollapsesResult(): void
	{
		$array = [
			'posts' => [
				[
					'comments' => [
						['author' => 'jose', 'likes' => 4],
						['author' => 'daniel', 'likes' => 3],
					],
				],
				[
					'comments' => [
						['author' => 'daniel', 'likes' => 2],
						['author' => 'david'],
					],
				],
				[
					'comments' => [
						['author' => 'david'],
						['author' => 'jose', 'likes' => 1],
					],
				],
			],
		];

		$this->assertEquals(['jose', 'daniel', 'daniel', 'david', 'david', 'jose'], Data::get($array, 'posts.*.comments.*.author'));
		$this->assertEquals([4, 3, 2, null, null, 1], Data::get($array, 'posts.*.comments.*.likes'));
		$this->assertEquals([], Data::get($array, 'posts.*.users.*.name', 'irrelevant'));
		$this->assertEquals([], Data::get($array, 'posts.*.users.*.name'));
	}

	public function testSet(): void
	{
		$data = ['foo' => 'bar'];

		$this->assertEquals(
			['foo' => 'bar', 'baz' => 'boom'],
			Data::set($data, 'baz', 'boom')
		);

		$this->assertEquals(
			['foo' => 'bar', 'baz' => 'kaboom'],
			Data::set($data, 'baz', 'kaboom')
		);

		$this->assertEquals(
			['foo' => [], 'baz' => 'kaboom'],
			Data::set($data, 'foo.*', 'noop')
		);

		$this->assertEquals(
			['foo' => ['bar' => 'boom'], 'baz' => 'kaboom'],
			Data::set($data, 'foo.bar', 'boom')
		);

		$this->assertEquals(
			['foo' => ['bar' => 'boom'], 'baz' => ['bar' => 'boom']],
			Data::set($data, 'baz.bar', 'boom')
		);

		$this->assertEquals(
			['foo' => ['bar' => 'boom'], 'baz' => ['bar' => ['boom' => ['kaboom' => 'boom']]]],
			Data::set($data, 'baz.bar.boom.kaboom', 'boom')
		);
	}

	public function testSetWithStar(): void
	{
		$data = ['foo' => 'bar'];

		$this->assertEquals(
			['foo' => []],
			Data::set($data, 'foo.*.bar', 'noop')
		);

		$this->assertEquals(
			['foo' => [], 'bar' => [['baz' => 'original'], []]],
			Data::set($data, 'bar', [['baz' => 'original'], []])
		);

		$this->assertEquals(
			['foo' => [], 'bar' => [['baz' => 'boom'], ['baz' => 'boom']]],
			Data::set($data, 'bar.*.baz', 'boom')
		);

		$this->assertEquals(
			['foo' => [], 'bar' => ['overwritten', 'overwritten']],
			Data::set($data, 'bar.*', 'overwritten')
		);
	}

	public function testSetWithDoubleStar(): void
	{
		$data = [
			'posts' => [
				(object) [
					'comments' => [
						(object) ['name' => 'First'],
						(object) [],
					],
				],
				(object) [
					'comments' => [
						(object) [],
						(object) ['name' => 'Second'],
					],
				],
			],
		];

		Data::set($data, 'posts.*.comments.*.name', 'Filled');

		$this->assertEquals([
			'posts' => [
				(object) [
					'comments' => [
						(object) ['name' => 'Filled'],
						(object) ['name' => 'Filled'],
					],
				],
				(object) [
					'comments' => [
						(object) ['name' => 'Filled'],
						(object) ['name' => 'Filled'],
					],
				],
			],
		], $data);
	}

	public function testFill(): void
	{
		$data = ['foo' => 'bar'];

		$this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], Data::fill($data, 'baz', 'boom'));
		$this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], Data::fill($data, 'baz', 'noop'));
		$this->assertEquals(['foo' => [], 'baz' => 'boom'], Data::fill($data, 'foo.*', 'noop'));
		$this->assertEquals(
			['foo' => ['bar' => 'kaboom'], 'baz' => 'boom'],
			Data::fill($data, 'foo.bar', 'kaboom')
		);
	}

	public function testFillWithStar(): void
	{
		$data = ['foo' => 'bar'];

		$this->assertEquals(
			['foo' => []],
			Data::fill($data, 'foo.*.bar', 'noop')
		);

		$this->assertEquals(
			['foo' => [], 'bar' => [['baz' => 'original'], []]],
			Data::fill($data, 'bar', [['baz' => 'original'], []])
		);

		$this->assertEquals(
			['foo' => [], 'bar' => [['baz' => 'original'], ['baz' => 'boom']]],
			Data::fill($data, 'bar.*.baz', 'boom')
		);

		$this->assertEquals(
			['foo' => [], 'bar' => [['baz' => 'original'], ['baz' => 'boom']]],
			Data::fill($data, 'bar.*', 'noop')
		);
	}

	public function testFillWithDoubleStar(): void
	{
		$data = [
			'posts' => [
				(object) [
					'comments' => [
						(object) ['name' => 'First'],
						(object) [],
					],
				],
				(object) [
					'comments' => [
						(object) [],
						(object) ['name' => 'Second'],
					],
				],
			],
		];

		Data::fill($data, 'posts.*.comments.*.name', 'Filled');

		$this->assertEquals([
			'posts' => [
				(object) [
					'comments' => [
						(object) ['name' => 'First'],
						(object) ['name' => 'Filled'],
					],
				],
				(object) [
					'comments' => [
						(object) ['name' => 'Filled'],
						(object) ['name' => 'Second'],
					],
				],
			],
		], $data);
	}

}

/**
 * Class SupportTestArrayAccess
 *
 * @package Twist\Test\Library\Support
 */
class SupportTestArrayAccess implements ArrayAccess
{

	protected $attributes = [];

	public function __construct($attributes = [])
	{
		$this->attributes = $attributes;
	}

	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->attributes);
	}

	public function offsetGet($offset)
	{
		return $this->attributes[$offset];
	}

	public function offsetSet($offset, $value): void
	{
		$this->attributes[$offset] = $value;
	}

	public function offsetUnset($offset): void
	{
		unset($this->attributes[$offset]);
	}

}
