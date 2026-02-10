<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;

enum Active: string implements LabeledEnum
{
	use Traits\Labeled {
		getOptions as private traitOptions;
	}

	case All = '';
	case Yes = '1';
	case No = '0';


	public static function getOptions(bool $badge = false): array
	{
		return self::traitOptions($badge);
	}


	public function label(): string
	{
		return match ($this) {
			self::All => 'web.general.all',
			self::Yes => 'web.general.yes',
			self::No => 'web.general.no',
		};
	}


	public function icon(): string
	{
		return match ($this) {
			self::All => 'fa-circle-notch',
			self::Yes => 'fa-check',
			self::No => 'fa-times',
		};
	}


	public function color(): Color
	{
		return match ($this) {
			self::All => Color::Secondary,
			self::Yes => Color::Success,
			self::No => Color::Danger,
		};
	}
}
