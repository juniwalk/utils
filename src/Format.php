<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Currency;

final class Format
{
	public static function phoneNumber(?string $phone): ?string
	{
		static $formats = [
			['+420', '(\d{3})(\d{3})(\d{3})', '%s %s %s %s'],	// Czechia
			['+421', '(\d{4})(\d{3})(\d{3})', '%s %s %s %s'],	// Slovakia
			['+49', '(0?\d{3})(\d{7})', '%s %s %s'],			// Germany
			['', '(\d{3})(\d{3})(\d+)', '%s%s %s %s'],			// default
		];

		if (!$phone || !$phone = str_replace(' ', '', $phone)) {
			return null;
		}

		foreach ($formats as [$prefix, $pattern, $format]) {
			if (!Strings::startsWith($phone, $prefix)) {
				continue;
			}

			$quote = preg_quote($prefix);

			if (!$params = Strings::match($phone, '/^'.$quote.$pattern.'$/')) {
				continue;
			}

			$params[0] = $prefix;

			return sprintf($format, ... $params);
		}

		return $phone;
	}


	public static function size(int $bytes, int $decimals = 2): string
	{
		static $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		$factor = floor((strlen((string) $bytes) - 1) / 3);

		if ($factor <= 0) {
			$decimals = 0;
		}

		return static::value(
			$bytes / pow(1024, $factor),
			$size[$factor],
			$decimals,
			'%1$s%2$s',
		);
	}


	public static function number(float $value, int $decimals = 2): string
	{
		static $size = ['', 'k', 'M', 'B', 'T', 'Q', 'S', 'O', 'N'];
		$factor = floor((strlen((string) intval($value)) - 1) / 3);

		return static::value(
			$value / pow(1000, $factor),
			$size[$factor],
			$decimals,
			'%1$s%2$s',
		);
	}


	public static function currency(float $value, Currency $unit, int $decimals = 2): string
	{
		return static::value($value, $unit->label(), $decimals, $unit->format());
	}


	public static function value(float $value, string $unit, int $decimals = 2, string $format = '%1$s %2$s'): string
	{
		$value = number_format($value, $decimals, ',', ' ');
		return sprintf($format, $value, $unit);
	}
}
