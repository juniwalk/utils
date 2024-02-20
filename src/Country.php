<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Exceptions\CountryException;

final class Country
{
	private static string $packagePath = '../vendor/umpirsky/country-list';
	private static array $cache = [];


	/**
	 * @throws CountryException
	 */
	public static function setPath(string $path): void
	{
		if (!is_dir($path) || !Strings::match($path, '/umpirsky\/country-list$/i')) {
			throw CountryException::packagePathInvalid($path);
		}

		self::$packagePath = $path;
	}


	/**
	 * @throws CountryException
	 */
	public static function getList(string $locale, string $format = 'php'): array|string
	{
		$path = sprintf(self::$packagePath.'/data/%s/country.%s', $locale, $format);

		if (!file_exists($path)) {
			throw CountryException::listNotFound($path);
		}

		return self::$cache[$format] ??= match($format) {
			'php' => include $path,
			default => file_get_contents($path),
		};
	}
}
