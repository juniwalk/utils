<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Exceptions;

final class CountryException extends \RuntimeException
{
	public static function listNotFound(string $lang, string $format, string $path): self
	{
		return new static('Country list for "'.$lang.'" using "'.$format.'" at "'.$path.'" was not found.');
	}


	public static function packagePathInvalid(string $path): self
	{
		return new static('Invalid path to "umpirsky/country-list" package at "'.$path.'"');
	}
}
