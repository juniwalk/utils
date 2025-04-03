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
use Throwable;

/**
 * @link https://semver.org/
 */
final class Version implements Stringable
{
	private const Pattern = '/^v?(?<major>[0-9]+).(?<minor>[0-9]+).?(?<patch>[0-9]*)([+.-]*(?<preRelease>[a-z][a-z0-9]*))?.?(?<build>[0-9]*)$/i';

	public const SemVer = '%M.%m.%p-%r.%b';
	public const Tag = 'v%M.%m.%p-%r.%b';
	public const Dev = 'v%M.%m.x-%r.%b';

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
		$schema = VersionSchema::fromFile($file);

		if (!$schema->tag) {
			return null;
		}

		return new static($schema->tag);
	}


	public static function getVersion(string|object $json, string $format = self::Tag): ?string
	{
		try {
			$schema = match (true) {
				is_object($json) => VersionSchema::fromObject($json),
				is_string($json) => VersionSchema::fromFile($json),
			};

			$schema->branch ??= $schema->tag ?: 'master';

		} catch (Throwable) {
			return null;
		}

		try {
			$version = new static($schema->tag);
			$branch = new static($schema->branch);

		} catch (Throwable) {
		}

		if (!$schema->tag && !($branch ?? null)) {
			return 'dev-'.$schema->branch.'@'.$schema->hash;
		}

		$version ??= null;

		if ($schema->isDirty || $schema->commits > 0) {
			$version = ($branch ?? $version)?->advance(Strategy::Build, 'dev', $schema->commits);
			$format = static::Dev;
		}

		return $version?->format($format);
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
		return $this->format(static::Tag);
	}


	public function getMajor(): ?int
	{
		return $this->major;
	}


	public function getMinor(): ?int
	{
		return $this->minor;
	}


	public function getPatch(): ?int
	{
		return $this->patch;
	}


	public function getBuild(): ?int
	{
		return $this->build;
	}


	public function isPreRelease(): bool
	{
		return $this->major === 0 || isset($this->preRelease) || $this->build > 0;
	}


	/**
	 * @throws VersionInvalidException
	 */
	public function parse(self|string|null $version = null): static
	{
		$parts = Strings::match((string) $version, static::Pattern);

		if (!$version || empty($parts)) {
			throw VersionInvalidException::fromVersion($version);
		}

		foreach ($parts as $part => $value) {
			if (!is_string($part) || !property_exists($this, $part)) {
				continue;
			}

			$this->$part = match ($part) {
				'major', 'minor',
				'patch', 'build' => intval($value),
				'preRelease' => strval($value),
			};

			if ($value === '') {
				$this->$part = null;
			}
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

		return str_replace('-.', '.', trim($version, '+.-'));
	}


	public function advance(Strategy $strategy, ?string $preRelease = null, ?int $value = null): static
	{
		if (!$preRelease && $strategy <> Strategy::Build) {
			$this->preRelease = null;
			$this->build = null;
		}

		$this->{$strategy->value} += 1;

		switch ($strategy) {
			// ! Intentionally no break statements to fall through
			case Strategy::Major: $this->minor = 0;
			case Strategy::Minor: $this->patch = 0;
			case Strategy::Patch: $this->build = null;
		}

		if ($preRelease && ($preRelease <> $this->preRelease || !$this->build)) {
			$this->preRelease = $preRelease;
			$this->build = 1;
		}

		if (!is_null($value)) {
			$this->{$strategy->value} = abs($value);
		}

		return $this;
	}


	/**
	 * @return ($operator is null ? int : bool)
	 */
	public function compare(self|string $version, ?string $operator = null): int|bool	// @phpstan-ignore return.unusedType
	{
		if ($version instanceof static) {
			$version = $version->format(static::SemVer);
		}

		return version_compare(
			$this->format(static::SemVer),
			$version,
			$operator,
		);
	}
}
