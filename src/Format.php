<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Interfaces\Currency;

final class Format
{
	public static function snakeCase(string $value): string
	{
		return strtolower(preg_replace('/[A-Z]/', '_$0', $value));
	}


	public static function camelCase(string $value): string
	{
		return lcfirst(implode('', array_map('ucfirst', explode('_', $value))));
	}


	public static function phoneNumber(?string $value, bool $clearUnmatched = false): ?string
	{
		static $formats = [
			'de' => ['+49', '(0?\d{2,3})(\d{4})(\d{4})', '%s %s %s', '{10,11}'],
			'pl' => ['+48', '(\d{3})(\d{3})(\d{3})', '%s %s %s', '{9}'],
			'se' => ['+46', '(0?\d{2,3})(\d{3})(\d{2,3})', '%s %s %s', '{7,13}'],
			'at' => ['+43', '(\d{3})(\d{4})(\d{0,4})', '%s %s %s', '{7,13}'],
			'sk' => ['+421', '0?(\d{3})(\d{3})(\d{3})', '%s %s %s', '{9}'],
			'cz' => ['+420', '(\d{3})(\d{3})(\d{3})', '%s %s %s', '{9}'],
			'it' => ['+39', '(0\d{1}|3\d{2})(\d{4})(\d{3,4})', '%s %s %s', '{9,10}'],
			'lt' => ['+370', '8?(\d{1,3})(\d{1,3})(\d{4})', '%s %s%s', '{8}'],
			'fr' => ['+33', '0?([1-9])(\d{2})(\d{2})(\d{2})(\d{2})', '%s %s %s %s %s', '{9}'],
			'be' => ['+32', '0?([^4]\d{0,1}|[4]\d{2})(\d{2,3})(\d{2})(\d{2})', '%s %s %s %s', '{9,10}'],
			'nl' => ['+31', '0?([6]|\d{2})(\d{4})(\d{3,4})', '%s %s %s', '{9}'],
			null => ['', '([2-7]\d{2})(\d{3})(\d{3})', '%s %s %s', '{9}'],
		];

		foreach ($formats as [$area]) {
			$value = static::areaCode($value, $area) ?? $value;
		}

		if (!$value = Sanitize::phoneNumber($value)) {
			return null;
		}

		foreach ($formats as [$area, $regex, $format, $length]) {
			if (!$match = Strings::match($value, '/^'.preg_quote($area).$regex.'(?:.*)$/')) {
				continue;
			}

			unset($match[0]);

			$number = sprintf($format, ... $match);
			$phone = Sanitize::phoneNumber($number);

			if (!Strings::match($phone, '/^[0-9]'.$length.'$/')) {
				continue;
			}

			$area = $area ?: $formats['cz'][0];
			return $area.' '.$number;
		}

		if ($clearUnmatched) {
			return null;
		}

		return $value;
	}


	public static function areaCode(?string $value, string $area): ?string
	{
		$regex = sprintf('/^(00%1$s|\(%1$s\))/', trim($area, '+'));

		if (!$area || !$match = Strings::match($value ?? '', $regex)) {
			return null;
		}

		return str_replace($match[0], $area, $value);
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


	public static function number(float|int $value, int $decimals = 2): string
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


	public static function price(float|int $value, Currency $unit, int $decimals = 2): string
	{
		return static::value($value, $unit->label(), $decimals, $unit->format());
	}


	/**
	 * @deprecated
	 */
	public static function currency(float $value, Currency $unit, int $decimals = 2): string
	{
		trigger_error('Method '.__METHOD__.' is deprecated use price instead', E_USER_DEPRECATED);
		return static::price($value, $unit, $decimals);
	}


	public static function value(float|int $value, string $unit, int $decimals = 2, string $format = '%1$s %2$s'): string
	{
		$value = number_format($value, $decimals, ',', ' ');
		return sprintf($format, $value, $unit);
	}
}
