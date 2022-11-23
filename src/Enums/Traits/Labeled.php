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


	public static function tryMake(mixed $value): ?static
	{
		try {
			return static::make($value);

		} catch (TypeError|ValueError) {}

		return null;
	}


	/**
	 * @throws ValueError
	 */
	public static function make(mixed $value): static
	{
		if ($value instanceof static) {
			return $value;
		}

		if ($case = static::tryFrom($value)) {
			return $case;
		}

		$lower = Strings::lower((string) $value);

		if ($case = static::tryFrom($lower)) {
			return $case;
		}

		foreach (static::cases() as $case) {
			if ($case->name == $value) {
				return $case;
			}

			continue;
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
