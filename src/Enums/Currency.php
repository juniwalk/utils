<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

enum Currency: string implements Interfaces\Currency
{
	use Traits\Labeled;

	case CZK = 'czk';
	case EUR = 'eur';
	case USD = 'usd';


	public function label(): string
	{
		return $this->name;
	}


	public function format(): string
	{
		return '%1$s %2$s';
	}


	public function color(): Color
	{
		return Color::Secondary;
	}
}
