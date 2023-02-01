<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Strategy;
use JuniWalk\Utils\Exceptions\VersionInvalidException;
use Stringable;
use ValueError;

/**
 * @link https://semver.org/
 */
final class Version implements Stringable
{
	private const PATTERN = '/^v?(?<major>[0-9]+).(?<minor>[0-9]+).?(?<patch>[0-9]*)[+.-]*(?<preRelease>[a-z]*[a-z0-9]*).?(?<build>[0-9]*)$/i';

	final public const SEMVER = '%M.%m.%p-%r.%b';
	final public const TAG = 'v%M.%m.%p-%r.%b';

	private ?string $preRelease = null;
	private ?int $major = null;
	private ?int $minor = null;
	private ?int $patch = null;
	private ?int $build = null;


	/**
	 * @throws VersionInvalidException
	 */
	public static function fromFile(string $file): ?static
	{
		try {
			$result = Json::decodeFile($file);

		} catch (Throwable) {
			throw VersionInvalidException::fromFile($file);
		}

		if (!$result->tag) {
			return null;
		}

		return new static($result->tag);
	}


	/**
	 * @throws VersionInvalidException
	 */
	public function __construct(?string $version)
	{
		try {
			$version && Strategy::from($version);
			$version = null;

		} catch (ValueError) {}

		$version && $this->parse($version);
	}


	public function __toString(): string
	{
		return $this->format(static::TAG);
	}


	public function isPreRelease(): bool
	{
		return isset($this->preRelease) || $this->build > 0;
	}


	/**
	 * @throws VersionInvalidException
	 */
	public function parse(self|string $version = null): static
	{
		$parts = Strings::match((string) $version, static::PATTERN);

		if (!$version || empty($parts)) {
			throw VersionInvalidException::fromVersion($version);
		}

		foreach ($parts as $part => $value) {
			if (!is_string($part) || !property_exists($this, $part)) {
				continue;
			}

			if (is_numeric($value)) {
				$value = (int) $value;
			}

			$this->$part = $value !== '' ? $value : null;
		}

		return $this;
	}


	public function format(string $format): string
	{
		$version = strtr($format, [
			'%r' => $this->preRelease,
			'%M' => $this->major,
			'%m' => $this->minor,
			'%p' => $this->patch,
			'%b' => $this->build,
		]);

		return trim($version, '+.-');
	}


	public function advance(Strategy $strategy, ?string $preRelease = null): static
	{
		if (!$preRelease && $strategy <> Strategy::Build) {
			$this->preRelease = null;
			$this->build = null;
		}

		$this->{$strategy->value} += 1;

		switch ($strategy) {
			case Strategy::Major: $this->minor = 0;
			case Strategy::Minor: $this->patch = 0;
			case Strategy::Patch: $this->build = null;
		}

		if ($preRelease && ($preRelease <> $this->preRelease || !$this->build)) {
			$this->preRelease = $preRelease;
			$this->build = 1;
		}

		return $this;
	}


	public function compare(self|string $version, ?string $operator = null): bool|int
	{
		if ($version instanceof static) {
			$version = $version->format(static::SEMVER);
		}

		return version_compare(
			$this->format(static::SEMVER),
			$version,
			$operator
		);
	}
}
