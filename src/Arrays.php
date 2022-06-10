<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Stringable;

final class Arrays
{
	/**
	 * @param  mixed[]  $array
	 * @param  string|null  $prefix
	 * @return mixed[]
	 */
	public static function flatten(iterable $items, string $prefix = null): iterable
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
	 * @param  mixed[]  $items
	 * @return mixed[]
	 */
	public static function unflatten(iterable $items): iterable
	{
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


	/**
	 * @param  mixed[]  $items
	 * @return string[]
	 */
	public static function tokenize(iterable $items): iterable
	{
		$result = [];

		foreach($items as $key => $value) {
			if (!is_scalar($value) && !$value instanceof Stringable) {
				continue;
			}

			$result['{'.$key.'}'] = $value;
		}

		return $result;
	}
}
