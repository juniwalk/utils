<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

final class Sanitize
{
	public static function phoneNumber(?string $value): ?string
	{
		if (!$value) {
			return null;
		}

		$hasPrefix = Strings::startsWith($value, '+');
		$value = Strings::replace($value, '/[^0-9]/');

		return ($hasPrefix ? '+' : null).$value;
	}
}
