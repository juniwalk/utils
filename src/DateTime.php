<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

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
}
