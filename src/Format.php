<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

final class Format
{
	public static function price(float $value, string $unit, int $decimals = 2, string $format = '%1$s %2$s'): string
	{
		$value = number_format($value, $decimals, ',', ' ');
		return sprintf($format, $value, $unit);
	}


	public static function number(float $value, int $decimals = 2, string $format = '%g%s'): string
	{
		$size = ['', 'k', 'M', 'B', 'T', 'Q', 'S', 'O', 'N'];
		$factor = floor((strlen((string) intval($value)) - 1) / 3);

		$value = $value / pow(1000, $factor);
		$value = round($value, $decimals);
		return sprintf($format, $value, $size[$factor]);
	}


	public static function size(int $bytes, int $decimals = 2): string
	{
		$size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		$factor = floor((strlen((string) $bytes) - 1) / 3);

		if ($factor <= 0) {
			$decimals = 0;
		}

		return sprintf(
			'%.'.$decimals.'f '.$size[$factor],
			$bytes / pow(1024, $factor)
		);
	}
}
