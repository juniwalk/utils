<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Exceptions;

use JuniWalk\Utils\Enums\Strategy;
use JuniWalk\Utils\Version;

final class VersionInvalidException extends \RuntimeException
{
	public static function fromVersion(Version|string $version = null): static
	{
		return new static('Invalid version "'.$version.'" given');
	}


	public static function fromStrategy(Version|string $version = null, Strategy $strategy): static
	{
		if ($version === null) {
			$version = 'non-existing version';
		}

		return new static('Cannot advance "'.$version.'" using "'.$strategy->value.'" strategy, please use exact version');
	}


	public static function fromFile(string $file): static
	{
		return new static('Unable to read version.json from file "'.$file.'"');
	}


	public static function fromCompare(
		Version|string $version1,
		Version|string $version2,
		?string $operator = null
	): static {
		return new static('Version comparison failed: '.$version1.' '.$operator.' '.$version2);
	}
}
