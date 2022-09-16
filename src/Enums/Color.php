<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

enum Color: string
{
	/** Basic Bootstrap colors */
	case Primary = 'primary';
	case Secondary = 'secondary';
	case Info = 'info';
	case Success = 'success';
	case Warning = 'warning';
	case Danger = 'danger';
	case Light = 'light';
	case Dark = 'dark';

	/** Extended AdminLTE colors */
	case Indigo = 'indigo';
	case LightBlue = 'lightblue';
	case Navy = 'navy';
	case Purple = 'purple';
	case Fuchsia = 'fuchsia';
	case Pink = 'ping';
	case Maroon = 'maroon';
	case Orange = 'orange';
	case Lime = 'lime';
	case Teal = 'teal';
	case Olive = 'olive';

	/** Black & White AdminLTE colors */
	case Black = 'black';
	case GrayDark = 'gray-dark';
	case Gray = 'gray';


	public function for(string $type): string
	{
		if (!$this->isBasicColor()) {
			$type = 'bg';
		}

		return $type.'-'.$this->value;
	}


	private function isBasicColor(): bool
	{
		return match($this) {
			self::Primary, self::Secondary, self::Info,
			self::Success, self::Warning, self::Danger,
			self::Light, self::Dark => true,
			default => false,
		};
	}
}
