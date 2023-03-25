<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use JuniWalk\Utils\Arrays;
use JuniWalk\Utils\Strings;

enum Color: string implements LabeledEnum
{
	use Traits\Labeled;

	case Primary = 'primary';
	case Secondary = 'secondary';
	case Info = 'info';
	case Success = 'success';
	case Warning = 'warning';
	case Danger = 'danger';
	case Indigo = 'indigo';
	case LightBlue = 'lightblue';
	case Navy = 'navy';
	case Purple = 'purple';
	case Fuchsia = 'fuchsia';
	case Pink = 'pink';
	case Maroon = 'maroon';
	case Orange = 'orange';
	case Lime = 'lime';
	case Teal = 'teal';
	case Olive = 'olive';
	case GrayDark = 'gray-dark';


	public function label(): string
	{
		return 'web.enum.color.'.$this->value;
	}


	public function color(): Color
	{
		return $this;
	}


	public function icon(): ?string
	{
		return 'fa-droplet fas';
	}


	public function for(string $type): string
	{
		if ($type <> 'text' && !$this->isBasicColor()) {
			$type = 'bg';
		}

		return $type.'-'.$this->value;
	}


	public function hex(): string
	{
		return match($this) {
			self::Primary => '#007bff',
			self::Secondary => '#6c757d',
			self::Info => '#17a2b8',
			self::Success => '#28a745',
			self::Warning => '#ffc107',
			self::Danger => '#dc3545',
			self::Indigo => '#6610f2',
			self::LightBlue => '#3c8dbc',
			self::Navy => '#001f3f',
			self::Purple => '#6f42c1',
			self::Fuchsia => '#f012be',
			self::Pink => '#e83e8c',
			self::Maroon => '#d81b60',
			self::Orange => '#fd7e14',
			self::Lime => '#01ff70',
			self::Teal => '#20c997',
			self::Olive => '#3d9970',
			self::GrayDark => '#343a40',
		};
	}


	public function foreground(): string
	{
		return $this->luminosity() > 0.5
			? '#343a40'		// Dark
			: '#f8f9fa';	// Light
	}


	private function isBasicColor(): bool
	{
		return match($this) {
			self::Primary, self::Secondary,
			self::Info, self::Success,
			self::Warning, self::Danger => true,
			default => false,
		};
	}


	private function luminosity(): float
	{
		$mod = [0.2126, 0.7152, 0.0722];
		$hex = Strings::split($this->hex(), '/\#?([a-f0-9]{2})/', PREG_SPLIT_NO_EMPTY);
		$rgb = Arrays::map($hex, fn(string $v, int $k): float => (hexdec($v) / 255) * $mod[$k]);
		return array_sum($rgb);
	}
}
