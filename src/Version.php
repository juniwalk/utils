<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Strategy;
use Stringable;

final class Version implements Stringable
{
	private const PATTERN = '/(?<version>v(?<major>\d+)\.?(?<minor>\d+)?\.?(?<patch>\d+)?)\-?(?<dev>(?<tag>alpha|beta|dev|rc)?\.?(?<build>\d+)?)?/i';
	private const STRUCTURE = 'vmajor.minor.patch-tag.build';

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
		return $this->build();
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


	public function parse(self|string $version): static
	{
		if ($version instanceof static) {
			$version = $version->build();
		}

		$parts = Strings::match($version, static::PATTERN, PREG_UNMATCHED_AS_NULL);

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


	public function build(): string
	{
		$version = strtr(static::STRUCTURE, [
			'major' => $this->major,
			'minor' => $this->minor,
			'patch' => $this->patch,
			'build' => $this->build,
			'tag' => $this->tag,
		]);

		return trim($version, '-.');
	}


	public function compare(self $version, ?string $operator = null): bool|int
	{
		return version_compare($this->build(), $version->build(), $operator);
	}
}
