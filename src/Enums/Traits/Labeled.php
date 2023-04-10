<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums\Traits;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Strings;
use TypeError;
use ValueError;

trait Labeled
{
	public static function getLabels(): iterable
	{
		$labels = [];

		foreach (self::cases() as $case) {
			$labels[$case->value] = $case->label();
		}

		return $labels;
	}


	/**
	 * @deprecated
	 */
	public static function tryMake(mixed $value): ?static
	{
		// todo add hard deprecate in future versions
		return static::make($value, false);
	}


	/**
	 * @throws ValueError
	 */
	public static function make(mixed $value, bool $required = true): ?static
	{
		if ($value instanceof static) {
			return $value;
		}

		$value = (string) $value;

		foreach (static::cases() as $case) {
			if (Strings::compare((string) $case->value, $value)) {
				return $case;
			}

			if (Strings::compare($case->name, $value)) {
				return $case;
			}

			continue;
		}

		if (!$required) {
			return null;
		}

		throw new ValueError('"'.$value.'" is not a valid backing value for enum "'.static::class.'"');
	}


	public function color(): ?Color
	{
		return null;
	}


	public function icon(): ?string
	{
		return null;
	}
}
