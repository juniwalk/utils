<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Iterator;
use Stringable;
use UnexpectedValueException;

final class Arrays
{
	public static function map(iterable $items, callable $callback, bool $isRecursive = true): iterable
	{
		$callback = function(mixed $value, mixed $key) use ($callback, $isRecursive) {
			if (!$isRecursive || !is_iterable($value)) {
				return $callback($value, $key);
			}

			return static::map($value, $callback, true);
		};

		foreach($items as $key => $value) {
			$items[$key] = $callback($value, $key);
		}

		return $items;
	}


	/**
	 * @throws UnexpectedValueException
	 */
	public static function walk(array $items, callable $callback): array
	{
		$result = [];
		$callback = function(mixed $value, mixed $key) use ($callback): array {
			$items = $callback($value, $key);

			if (!$items instanceof Iterator) {
				throw new UnexpectedValueException('Callback is expected to return instance of Iterator');
			}

			return iterator_to_array($items);
		};

		foreach ($items as $key => $value) {
			$yield = $callback($value, $key);

			if (!key($yield)) {
				$result[] = current($yield);
				continue;
			}

			$result = $result + $yield;
		}

		return $result;
	}


	public static function intersect(array $items, array $array, bool $isRecursive = true): iterable
	{
		$callback = function(array $a1, array $a2): array {
			$a1 = array_intersect_key($a1, $a2);
			return static::walk($a2, fn($v, $k) => yield $k => $a1[$k] ?? $v);
		};

		$items = $callback($items, $array);

		if (!$isRecursive) {
			return $items;
		}

		foreach ($items as $key => $values) {
			if (!is_array($values) || !$data = $array[$key] ?? null) {
				continue;
			}

			$items[$key] = static::intersect($values, $data, $isRecursive);
		}

		return $items;
	}


	public static function flatten(iterable $items, string $prefix = null): array
	{
		$result = [];

		foreach($items as $key => $value) {
			if (!is_iterable($value)) {
				$result[$prefix.$key] = $value;
				continue;
			}

			$result = $result + static::flatten($value, $prefix.$key.'.');
		}

		return $result;
	}


	public static function unflatten(iterable $items): array
	{
		$items = static::flatten($items);
		$result = [];

		foreach($items as $key => $value) {
			$parts = explode('.', $key);
			$current = &$result;

			foreach($parts as $part) {
				$current = &$current[$part];
			}

			$current = $value;
		}

		return $result;
	}


	public static function tokenize(iterable $items, string $token = '{%s}'): array
	{
		$result = [];

		foreach(static::flatten($items) as $key => $value) {
			if (!is_scalar($value) && !$value instanceof Stringable) {
				continue;
			}

			$result[sprintf($token, $key)] = $value;
		}

		return $result;
	}
}
