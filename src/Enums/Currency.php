<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use JuniWalk\Utils\Enums\Interfaces\Currency as CurrencyInterface;
use ValueError;

enum Currency: string implements CurrencyInterface
{
	use Traits\Labeled;

	case CZK = 'czk';
	case EUR = 'eur';
	case USD = 'usd';


	/**
	 * @param  int|string|CurrencyInterface|null $value
	 * @return ($required is true ? CurrencyInterface : ?CurrencyInterface)
	 * @throws ValueError
	 */
	public static function remake(mixed $value, bool $required = true): ?CurrencyInterface
	{
		if ($value instanceof CurrencyInterface) {
			return $value;
		}

		return static::make($value, $required);
	}


	public function label(): string
	{
		return $this->name;
	}


	public function format(): string
	{
		return '%1$s %2$s';
	}


	public function formatInLocale(): string
	{
		return match($this) {
			self::CZK => '%1$s Kč',
			self::EUR => '€%1$s',
			self::USD => '$%1$s',

			// default => '%1$s %2$s',
		};
	}


	public function color(): Color
	{
		return Color::Secondary;
	}
}
