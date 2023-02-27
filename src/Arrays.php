<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Generator;
use Stringable;

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


	public static function walk(array $items, callable $callback): array
	{
		$callback = fn(mixed $value, mixed $key): Generator => yield from $callback($value, $key);
		$result = [];

		foreach ($items as $key => $value) {
			$result += iterator_to_array(
				$callback($value, $key)
			);
		}

		return $result;
	}


	public static function intersect(array $items, array $array, bool $isRecursive = true): iterable
	{
		$callback = function(array $a1, array $a2): array {
			$a1 = array_intersect_key($a1, $a2);
			$a1 = array_merge($a2, $a1);
			return $a1;
		};

		$items = $callback($items, $array);

		if (!$isRecursive) {
			return $items;
		}

		foreach ($items as $key => $values) {
			if (!$data = $array[$key] ?? null) {
				continue;
			}

			$items[$key] = $callback($values, $data);
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
