<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Strategy;
use Stringable;

/**
 * @link https://semver.org/
 */
final class Version implements Stringable
{
	private const PATTERN = '/^v?(?<major>[0-9]+).(?<minor>[0-9]+).?(?<patch>[0-9]*)[+.-]*(?<tag>[a-z]*[a-z0-9]*).?(?<build>[0-9]*)$/i';

	final public const SEMVER = '%M.%m.%p-%t.%b';
	final public const TAG = 'v%M.%m.%p-%t.%b';

	private ?int $major = null;
	private ?int $minor = null;
	private ?int $patch = null;
	private ?int $build = null;
	private ?string $tag = null;


	public function __construct(?string $version = null)
	{
		$version && $this->parse($version);
	}


	public function __toString(): string
	{
		return $this->format(static::TAG);
	}


	public function parse(self|string $version): static
	{
		$parts = Strings::match((string) $version, static::PATTERN);

		foreach ($parts ?: [] as $part => $value) {
			if (!is_string($part) || !property_exists($this, $part)) {
				continue;
			}

			if (is_numeric($value)) {
				$value = (int) $value;
			}

			$this->$part = $value;
		}

		return $this;
	}


	public function format(string $format): string
	{
		$version = strtr($format, [
			'%M' => $this->major,
			'%m' => $this->minor,
			'%p' => $this->patch,
			'%b' => $this->build,
			'%t' => $this->tag,
		]);

		return trim($version, '+.-');
	}


	public function advance(Strategy $strategy, ?string $tag = null): static
	{
		if (!$tag && $strategy <> Strategy::Build) {
			$this->build = null;
			$this->tag = null;
		}

		$this->{$strategy->value} += 1;

		switch ($strategy) {
			case Strategy::Major: $this->minor = 0;
			case Strategy::Minor: $this->patch = 0;
			case Strategy::Patch: $this->build = null;
		}

		if ($tag && ($tag <> $this->tag || !$this->build)) {
			$this->tag = $tag;
			$this->build = 1;
		}

		return $this;
	}


	public function compare(self $version, ?string $operator = null): bool|int
	{
		return version_compare(
			$this->format(static::SEMVER),
			$version->format(static::SEMVER),
			$operator
		);
	}
}
