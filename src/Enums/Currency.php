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
			self::CZK => 'CZK',
			self::EUR => 'EUR',
		};
	}


	public function color(): Color
	{
		return match($this) {
			self::CZK => Color::Secondary,
			self::EUR => Color::Secondary,
		};
	}
}
