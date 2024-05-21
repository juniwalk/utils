<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums\Traits;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;	// ! Used for @phpstan
use JuniWalk\Utils\Html;
use JuniWalk\Utils\Strings;
use Stringable;
use ValueError;

/**
 * @phpstan-require-implements LabeledEnum
 */
trait Labeled
{
	/**
	 * @return string[]
	 */
	public static function getLabels(): array
	{
		$items = [];

		foreach (self::cases() as $case) {
			$items[$case->value] = $case->label();
		}

		return $items;
	}


	/**
	 * @return Html[]
	 */
	public static function getOptions(bool $badge = true): array
	{
		$items = [];

		foreach (self::cases() as $case) {
			$items[$case->value] = Html::optionEnum($case, $badge);
		}

		return $items;
	}


	/**
	 * @return ($required is true ? static : ?static)
	 * @throws ValueError
	 */
	public static function make(mixed $value, bool $required = true): ?static
	{
		if ($value instanceof static) {
			return $value;
		}

		if ($value instanceof Stringable || is_string($value) || is_int($value)) {
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
		}

		if (!$required) {
			return null;
		}

		throw new ValueError('Given value is not a valid backing for enum "'.static::class.'"');
	}


	public function label(): string
	{
		return $this->name;
	}


	public function color(): Color
	{
		return Color::Secondary;
	}


	public function icon(): ?string
	{
		return null;
	}
}
