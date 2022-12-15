<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

enum Color: string implements LabeledEnum
{
	use Traits\Labeled;

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
		return 'fas fa-droplet';
	}


	public function for(string $type): string
	{
		if (!$this->isBasicColor()) {
			$type = 'bg';
		}

		return $type.'-'.$this->value;
	}


	public function hex(): string
	{
		return match($this) {
			/** Basic Bootstrap colors */
			self::Primary => '#007bff',
			self::Secondary => '#6c757d',
			self::Info => '#17a2b8',
			self::Success => '#28a745',
			self::Warning => '#ffc107',
			self::Danger => '#dc3545',
			self::Light => '#f8f9fa',
			self::Dark => '#343a40',

			/** Extended AdminLTE colors */
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
		
			/** Black & White AdminLTE colors */
			self::Black => '#000000',
			self::GrayDark => '#343a40',
			self::Gray => '#6c757d',
		};
	}


	public function foreground(): string
	{
		return match ($this) {
			self::Warning => self::Dark->hex(),
			self::Light => self::Dark->hex(),
			self::Teal => self::Dark->hex(),
			self::Lime => self::Dark->hex(),
			self::Orange => self::Dark->hex(),
			default => self::Light->hex(),
		};
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
