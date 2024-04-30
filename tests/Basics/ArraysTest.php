<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

use JuniWalk\Utils\Arrays;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class ArraysTest extends TestCase
{
	public function testMap(): void
	{
		$items = [2, 4, 8, 16, 32, 64];

		Assert::same(
			[1, 2, 4, 8, 16, 32],
			Arrays::map($items, fn($v) => $v/2)
		);
	}


	public function testMapRecursive(): void
	{
		$items = [[2, 4, 8], [16, 32, 64]];

		Assert::same(
			[[1, 2, 4], [8, 16, 32]],
			Arrays::mapRecursive($items, fn($v) => $v/2)
		);
	}


	public function testWalk(): void
	{
		$items = ['_first' => 'one', '_second' => 'two', '_third' => 'three', '_fourth' => ['_fifth' => 'four']];

		Assert::same(
			['one', 'two', 'three', ['_fifth' => 'four']],
			Arrays::walk($items, fn($v) => yield $v)
		);

		Assert::same(
			['first' => 'one', 'second' => 'two', 'third' => 'three', 'fourth' => ['_fifth' => 'four']],
			Arrays::walk($items, fn($v, $k) => yield trim($k, '_') => $v)
		);

		Assert::exception(
			fn() => Arrays::walk($items, fn($v) => $v),
			UnexpectedValueException::class,
			'Callback is expected to return Iterator',
		);

		Assert::exception(
			fn() => Arrays::walk($items, fn($v) => yield $this => $v),
			UnexpectedValueException::class,
			'Yielded key has to be of scalar type',
		);
	}


	public function testIntersect(): void
	{
		$items = ['_first' => 'one', '_second' => 'two', '_third' => ['_fourth' => 'three', '_fifth' => 'four']];
		$array = ['_second' => null, '_third' => ['_fifth' => null]];

		Assert::same(
			['_second' => 'two', '_third' => ['_fourth' => 'three', '_fifth' => 'four']],
			Arrays::intersect($items, $array, false)
		);

		Assert::same(
			['_second' => 'two', '_third' => ['_fifth' => 'four']],
			Arrays::intersect($items, $array)
		);
	}


	public function testFlatten(): void
	{
		$items = ['first' => ['second' => true, 'third' => 'fourth']];

		Assert::same(
			['first.second' => true, 'first.third' => 'fourth'],
			Arrays::flatten($items)
		);

		Assert::same(
			['_first.second' => true, '_first.third' => 'fourth'],
			Arrays::flatten($items, '_')
		);
	}


	public function testUnflatten(): void
	{
		$items = ['first.second' => true, 'first.third' => 'fourth'];

		Assert::same(
			['first' => ['second' => true, 'third' => 'fourth']],
			Arrays::unflatten($items)
		);
	}


	public function testTokenize(): void
	{
		$class = (object) ['alpha' => 'A', 'beta' => 'B', 'gamma' => 'G'];
		$items = ['first' => ['second' => true, 'third' => 'fourth'], 'fifth' => $class];

		Assert::same(
			['{first.second}' => true, '{first.third}' => 'fourth', '{fifth}' => $class],
			Arrays::tokenize($items)
		);
	}


	public function testCategorize(): void
	{
		$items = ['str_contains', 'str_ends_with', 'str_starts_with', 'array_merge'];

		Assert::same(
			['str' => ['str_contains', 'str_ends_with', 'str_starts_with'], 'arr' => ['array_merge']],
			Arrays::categorize($items, fn($v) => substr($v, 0, 3))
		);
	}
}

(new ArraysTest)->run();
