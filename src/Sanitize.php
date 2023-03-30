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
		$value = Strings::match($value ?: '', $regex)[1] ?? $value;
		$groups = Strings::split(
			Strings::toAscii($value ?: ''),
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


	public static function corporateId(?string $value, bool $clearInvalid = false): ?string
	{
		$isValid = function(string $cid, int $c = 0): bool {
			for ($n = 0; $n < 7; $n++) {
				$c += $cid[$n] * (8 - $n);
			}

			return (int) $cid[7] === (11 - ($c % 11)) % 10;
		};

		$value = Strings::replace($value ?: '', '/[^0-9]/');
		$value = Strings::padLeft($value, 8, '0');

		if (!($match = Strings::match($value, '/^[0-9]{8}/')) && !$clearInvalid) {
			return null;
		}

		$value = $match[0] ?? $value;

		if ($clearInvalid && !$isValid($value)) {
			return null;
		}

		return $value;
	}
}
