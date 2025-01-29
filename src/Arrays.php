<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Iterator;
use UnexpectedValueException;

final class Arrays
{
	/**
	 * @param  mixed[] $items
	 * @return mixed[]
	 */
	public static function map(iterable $items, callable $callback): array
	{
		$result = [];

		foreach ($items as $key => $value) {
			$result[$key] = $callback($value, $key);
		}

		return $result;
	}


	/**
	 * @param  mixed[] $items
	 * @return mixed[]
	 */
	public static function mapRecursive(iterable $items, callable $callback): array
	{
		$result = [];

		foreach ($items as $key => $value) {
			$result[$key] = match (true) {
				$value instanceof Html,
				!is_iterable($value) => $callback($value, $key),
				default => static::mapRecursive($value, $callback),
			};
		}

		return $result;
	}


	/**
	 * @param  mixed[] $items
	 * @return mixed[]
	 * @throws UnexpectedValueException
	 */
	public static function walk(iterable $items, callable $callback): array
	{
		$result = [];

		foreach ($items as $key => $value) {
			$yield = $callback($value, $key);

			if (!$yield instanceof Iterator) {
				throw new UnexpectedValueException('Callback is expected to return Iterator');
			}

			$key = $yield->key();

			if (!is_int($key) && !is_string($key)) {
				throw new UnexpectedValueException('Yielded key has to be of scalar type');
			}

			$result[] = [$key, $yield->current()];
		}

		$keys = array_column($result, 0);
		$vals = array_column($result, 1);

		if (!array_filter($keys)) {
			return $vals;
		}

		return array_combine($keys, $vals);
	}


	/**
	 * @param  mixed[] $items
	 * @param  mixed[] $array
	 * @return mixed[]
	 */
	public static function intersect(array $items, array $array): array
	{
		$items = array_intersect_key($items, $array);
		return static::walk($array, fn($v, $k) => yield $k => $items[$k] ?? $v);
	}


	/**
	 * @param  mixed[] $items
	 * @param  mixed[] $array
	 * @return mixed[]
	 */
	public static function intersectRecursive(array $items, array $array): array
	{
		$items = static::intersect($items, $array);

		foreach ($items as $key => $values) {
			if (!is_array($values) || !$data = $array[$key] ?? null) {
				continue;
			}

			// @phpstan-ignore-next-line
			$items[$key] = static::intersectRecursive($values, $data);
		}

		return $items;
	}


	/**
	 * @param  array<string, mixed> $items
	 * @return array<string, mixed>
	 */
	public static function flatten(iterable $items, ?string $prefix = null): array
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


	/**
	 * @param  array<string, mixed> $items
	 * @return array<string, mixed>
	 */
	public static function unflatten(iterable $items): array
	{
		$items = static::flatten($items);
		$result = [];

		foreach($items as $key => $value) {
			$parts = explode('.', $key);
			$current = &$result;

			foreach($parts as $part) {
				// ? Ignore unflattening if destination exists
				// ? but it is not an array to write into
				if (!is_array($current[$part] ?? [])) {
					$result[$key] = $value;
					continue 2;
				}

				$current = &$current[$part];
			}

			$current = $value;
		}

		return $result;
	}


	/**
	 * @param  array<string, mixed> $items
	 * @return array<string, mixed>
	 */
	public static function tokenize(iterable $items, string $token = '{%s}'): array
	{
		$chars = str_replace('%s', '', $token);
		$result = [];

		foreach (static::flatten($items) as $key => $value) {
			$key = sprintf($token, trim($key, $chars));
			$result[$key] = Format::serializable($value);
		}

		return $result;
	}


	/**
	 * @param  mixed[] $items
	 * @return array<int|string, array<int, mixed>>
	 */
	public static function categorize(array $items, callable $callback): array
	{
		$result = [];

		foreach ($items as $item) {
			if (!$key = Format::stringify($callback($item))) {
				continue;
			}

			$result[$key][] = $item;
		}

		ksort($result, SORT_NUMERIC);
		return $result;
	}
}
