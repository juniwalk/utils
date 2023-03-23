<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use DateTimeInterface;
use Nette\Utils\DateTime as NetteDate;

class DateTime extends NetteDate
{
	public static function tryFrom(mixed $time): ?static
	{
		if (is_null($time)) {
			return null;
		}

		return static::from($time);
	}


	public function timeFrom(?DateTimeInterface $date): static
	{
		$date ??= static::from('00:00:00');

		return $this->setTime(
			(int) $date->format('H'),
			(int) $date->format('i'),
			(int) $date->format('s'),
			(int) $date->format('u'),
		);
	}
}
