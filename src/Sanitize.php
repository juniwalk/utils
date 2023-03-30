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


	public static function emailAddress(?string $value): ?string
	{
		$regex = '/^(?:[^\<\[@]*)\s*(?:\<|\[)([^\>\]]+)(?:\>|\])$/iu';
		$match = Strings::match($value ?: '', $regex);
		$groups = Strings::split(
			Strings::toAscii($match[1] ?? $value),
			'/([^\s]+)\s*[;\/,]\s*([^\s]+)/i',
			PREG_SPLIT_NO_EMPTY
		);

		$regex = '/^[a-z0-9.!#$%&’*+\/\=?^_`{|}~-]+@[a-z0-9-]+(?:\.[a-z0-9-]+)*$/i';
		$value = $groups[0] ?? $value;

		if (!$value || !Strings::match($value, $regex)) {
			return null;
		}

		return Strings::lower($value);
	}
}
