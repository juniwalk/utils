<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

enum Currency: string implements LabeledEnum
{
	use Traits\Labeled;

	case EUR = 'eur';
	case USD = 'usd';
	case CZK = 'czk';


	public function label(): string
	{
		return $this->name;
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
