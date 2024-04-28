<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use DateTimeInterface;
use JuniWalk\Utils\Strings;
use Nette\Utils\DateTime as NetteDate;

class DateTime extends NetteDate
{
	public static function tryFrom(DateTimeInterface|int|string|null $time): ?static
	{
		if (is_null($time)) {
			return null;
		}

		return static::from($time);
	}


	public static function fromFileName(string $fileName, string $format = 'YmdHis'): ?static
	{
		$fileName = basename($fileName);
		$pattern = strtr($format, [
			'Y' => '([0-9]{4})',
			'm' => '([0-9]{2})',
			'd' => '([0-9]{2})',
			'H' => '([0-9]{2})',
			'i' => '([0-9]{2})',
			's' => '([0-9]{2})',
		]);

		if (!$matches = Strings::match($fileName, '/('.$pattern.')/i')) {
			return null;
		}

		return static::createFromFormat($format, $matches[0]) ?: null;
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
