<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Console\Enums;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum Status: string implements LabeledEnum
{
	use Labeled;

	case Working = '....';
	case Success = '<info> ok </>';
	case Warning = '<comment>warn</>';
	case Error = '<fg=red>FAIL</>';
	case Skipped = '<comment>skip</>';

	public function label(): string
	{
		return $this->name;
	}


	public function color(): Color
	{
		return match ($this) {
			self::Working => Color::Secondary,
			self::Success => Color::Success,
			self::Warning => Color::Warning,
			self::Error => Color::Danger,
			self::Skipped => Color::Warning,
		};
	}
}
