<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

enum Currency: string implements LabeledEnum
{
	use LabeledTrait;

	case EUR = 'eur';
	case USD = 'usd';
	case CZK = 'czk';


	public function label(): string
	{
		return match($this) {
			self::EUR => 'EUR',
			self::USD => 'USD',
			self::CZK => 'CZK',
		};
	}


	public function format(): string
	{
		return '%value% %unit%';
	}


	public function color(): Color
	{
		return Color::Secondary;
	}
}
