<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

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

		foreach($items as $key => $value) {
			if (!is_scalar($value) && !$value instanceof Stringable) {
				continue;
			}

			$result[sprintf($token, $key)] = $value;
		}

		return $result;
	}
}
