<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Exceptions\VersionInvalidException;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Throwable;
use stdClass;

final class VersionSchema
{
	public ?string $branch;
	public ?string $tag;
	public ?string $hash;
	public ?int $commits;
	public ?bool $isDirty;


	/**
	 * @throws VersionInvalidException
	 */
	public static function fromFile(string $file): static
	{
		try {
			/** @var stdClass */
			$json = Json::decodeFile($file);

			return static::fromObject($json);

		} catch (Throwable $e) {
			throw VersionInvalidException::fromFile($file, $e);
		}
	}


	/**
	 * @throws VersionInvalidException
	 */
	public static function fromObject(object $json): static
	{
		try {
			/** @var static */
			return (new Processor)->process(
				Expect::from(new static), $json,
			);

		} catch (Throwable $e) {
			throw $e;
		}
	}
}
