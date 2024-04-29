<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use DateTimeInterface;
use JsonSerializable;
use JuniWalk\Utils\Enums\Casing;
use JuniWalk\Utils\Enums\Interfaces\Currency;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Stringable;
use Throwable;
use UnexpectedValueException;
use UnitEnum;

final class Format
{
	/**
     * @template T of object
     * @param  T|class-string<T> $class
	 * @throws ReflectionException
	 */
	public static function className(object|string $class, Casing $case = Casing::Kebab, ?string $suffix = null): string
	{
		$name = (new ReflectionClass($class))->getShortName();

		if ($class instanceof Throwable) {
			$suffix = 'Exception';
		}

		if ($suffix <> null) {
			$name = Strings::replace($name, '/'.$suffix.'$/i', '');
		}

		return $case->format($name);
	}


	/**
	 * @example snake_case
	 */
	public static function snakeCase(string $value): string
	{
		return Casing::Snake->format($value);
	}


	/**
	 * @example kebab-case
	 */
	public static function kebabCase(string $value): string
	{
		return Casing::Kebab->format($value);
	}


	/**
	 * @example camelCase
	 */
	public static function camelCase(string $value): string
	{
		return Casing::Camel->format($value);
	}


	/**
	 * @example PascalCase
	 */
	public static function pascalCase(string $value): string
	{
		return Casing::Pascal->format($value);
	}


	/**
	 * @return scalar|stdClass
	 */
	public static function scalarize(mixed $value): mixed
	{
		return match (true) {
			!is_object($value),
			$value instanceof stdClass => $value,
			$value instanceof DateTimeInterface => $value->format('c'),
			$value instanceof JsonSerializable => $value->jsonSerialize(),
			$value instanceof Stringable => $value->__toString(),
			$value instanceof UnitEnum => $value->value ?? $value->name,

			method_exists($value, 'getId') => (int) $value->getId(),
			default => null,
		};
	}


	public static function stringify(mixed $value): string
	{
		$value = static::scalarize($value);

		if ($value instanceof stdClass) {
			$value = (array) $value;
		}

		return match (true) {
			is_array($value) => json_encode($value) ?: '',
			is_bool($value) => $value ? 'true' : 'false',
			default => (string) $value,
		};
	}


	public static function phoneNumber(?string $value, bool $clearUnmatched = false, bool $clearAreaCode = false): ?string
	{
		static $formats = [
			'de' => ['+49', '(0?\d{2,3})(\d{4})(\d{4})', '%s %s %s', '{10,11}'],
			'pl' => ['+48', '(\d{3})(\d{3})(\d{3})', '%s %s %s', '{9}'],
			'se' => ['+46', '(0?\d{2,3})(\d{3})(\d{2,3})', '%s %s %s', '{7,13}'],
			'at' => ['+43', '(\d{3})(\d{4})(\d{0,4})', '%s %s %s', '{7,13}'],
			'sk' => ['+421', '0?(\d{3})(\d{3})(\d{3})', '%s %s %s', '{9}'],
			'cz' => ['+420', '(\d{3})(\d{3})(\d{3})', '%s %s %s', '{9}'],
			'it' => ['+39', '(0\d{1}|3\d{2})(\d{4})(\d{3,4})', '%s %s %s', '{9,10}'],
			'lt' => ['+370', '8?(\d{1,3})(\d{1,3})(\d{4})', '%s %s%s', '{8}'],
			'fr' => ['+33', '0?([1-9])(\d{2})(\d{2})(\d{2})(\d{2})', '%s %s %s %s %s', '{9}'],
			'be' => ['+32', '0?([^4]\d{0,1}|[4]\d{2})(\d{2,3})(\d{2})(\d{2})', '%s %s %s %s', '{9,10}'],
			'nl' => ['+31', '0?([6]|\d{2})(\d{4})(\d{3,4})', '%s %s %s', '{9}'],
			null => ['', '([2-7]\d{2})(\d{3})(\d{3})', '%s %s %s', '{9}'],
		];

		foreach ($formats as [$area]) {
			$value = static::areaCode($value, $area) ?? $value;
		}

		if (!$value = Sanitize::phoneNumber($value)) {
			return null;
		}

		foreach ($formats as [$area, $regex, $format, $length]) {
			if (!$match = Strings::match($value, '/^'.preg_quote($area).$regex.'(?:.*)$/')) {
				continue;
			}

			unset($match[0]);

			$number = sprintf($format, ...$match);
			$phone = Sanitize::phoneNumber($number);

			if (!$phone || !Strings::match($phone, '/^[0-9]'.$length.'$/')) {
				continue;
			}

			if ($clearAreaCode) {
				return $number;
			}

			$area = $area ?: $formats['cz'][0];
			return $area.' '.$number;
		}

		if ($clearUnmatched) {
			return null;
		}

		return $value;
	}


	public static function areaCode(?string $value, string $area): ?string
	{
		$regex = sprintf('/^(00%1$s|\(%1$s\))/', trim($area, '+'));

		if (!$value || !$area || !$match = Strings::match($value, $regex)) {
			return null;
		}

		return str_replace($match[0], $area, $value);
	}


	/**
	 * @throws UnexpectedValueException
	 */
	public static function bytes(int $number, string $unit): float|int
	{
		return $number * pow(1024, match ($unit) {
			 'B' => 0, 'KB' => 1, 'MB' => 2,
			'GB' => 3, 'TB' => 4, 'PB' => 5,
			'EB' => 6, 'ZB' => 7, 'YB' => 8,

			default => throw new UnexpectedValueException(
				'Unit "'.$unit.'" is not supported.'
			),
		});
	}


	public static function size(int $bytes, int $decimals = 2, string $format = '%1$s%2$s'): string
	{
		static $unit = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		$factor = floor((strlen((string) $bytes) - 1) / 3);
		$value = $bytes / pow(1024, $factor);

		if ($factor <= 0) {
			$decimals = 0;
		}

		return static::value($value, $unit[$factor], $decimals, $format);
	}


	public static function number(float|int $value, int $decimals = 2, string $format = '%1$s%2$s'): string
	{
		static $unit = ['', 'k', 'M', 'B', 'T', 'Q', 'S', 'O', 'N'];
		$factor = floor((strlen((string) intval($value)) - 1) / 3);
		$value = $value / pow(1000, $factor);

		if ($value == intval($value)) {
			$decimals = 0;
		}

		return static::value($value, $unit[$factor], $decimals, $format);
	}


	public static function price(float|int $value, Currency $unit, int $decimals = 2, bool $localeAware = false): string
	{
		return static::value($value, $unit->label(), $decimals, $localeAware
			? $unit->formatInLocale()
			: $unit->format()
		);
	}


	public static function value(float|int $value, string $unit, int $decimals = 2, string $format = '%1$s %2$s'): string
	{
		$value = number_format($value, $decimals, ',', ' ');
		return sprintf($format, $value, $unit);
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public static function tokens(string $content, array $params = []): string
	{
		return strtr($content, Arrays::tokenize($params));
	}
}
