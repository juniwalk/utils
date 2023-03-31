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


	/**
	 * @see https://en.wikipedia.org/wiki/VAT_identification_number
	 */
	public static function vatNumber(?string $value, bool $clearUnmatched = false): ?string
	{
		static $formats = [
			'at' => 'U[0-9]{8}',
			'be' => '0[0-9]{9}',
			'bg' => '[0-9]{9,10}',
			'ch' => 'E?([0-9]{6}|[0-9]{9})(MWST|TVA|IVA)?',
			'cy' => '[0-9]{8}[a-z]',
			'cz' => '[0-9]{8,10}',
			'de' => '[0-9]{9}',
			'dk' => '[0-9]{8}',
			'ee' => '[0-9]{9}',
			'el' => '[0-9]{9}',
			'es' => '[0-9a-z][0-9]{7}[0-9a-z]',
			'eu' => '[0-9]{9}',
			'fi' => '[0-9]{8}',
			'fr' => '[0-9a-z][0-9a-z][0-9]{9}',	// No O / I
			'hr' => '[0-9]{11}',
			'hu' => '[0-9]{8}',
			'gb' => '[0-9]{9}',
			'ie' => '[0-9]{7}[a-z]{1,2}',
			'it' => '[0-9]{11}',
			'lt' => '[0-9]{9}|[0-9]{12}',
			'lu' => '[0-9]{8}',
			'lv' => '[0-9]{11}',
			'mt' => '[0-9]{8}',
			'nl' => '[0-9]{9}B[0-9]{2}',
			'no' => '[0-9]{9}(MVA)?',
			'pl' => '[0-9]{10}',
			'pt' => '[0-9]{9}',
			'ro' => '[0-9]{2,10}',
			'se' => '[0-9]{10}01',
			'si' => '[0-9]{8}',
			'sk' => '[0-9]{10}',
		];

		$value = Strings::replace($value ?: '', '/[^a-z0-9]/i');

		foreach ($formats as $code => $regex) {
			if (!$match = Strings::match($value, '/^('.$code.'('.$regex.'))$/i')) {
				continue;
			}

			return Strings::upper($match[1]);
		}

		if ($clearUnmatched) {
			return null;
		}

		return $value;
	}
}
