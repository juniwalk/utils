<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Nette\Utils\Json as NetteJson;
use Nette\Utils\JsonException;
use Nette\IOException;
use Stringable;

final class Json
{
	public const Pattern = '/(\"code\:([\/=+0-9a-z]+)\")/iU';
	public const Literal = 'code:';

	public const FORCE_ARRAY = NetteJson::FORCE_ARRAY;
	public const PRETTY = NetteJson::PRETTY;
	public const ESCAPE_UNICODE = NetteJson::ESCAPE_UNICODE;


	public static function literal(string $code): object
	{
		return new class($code) implements Stringable {
			public function __construct(
				private readonly string $code,
			) {
			}

			public function __toString(): string
			{
				return Json::Literal.base64_encode($this->code);
			}
		};
	}


	/**
	 * @throws JsonException
	 */
	public static function encode(mixed $content, int $flags = 0): string
	{
		if (!is_iterable($content)) {
			return NetteJson::encode($content, $flags);
		}

		$content = Arrays::mapRecursive($content, fn($v) => Format::serializable($v));
		$json = NetteJson::encode($content, $flags);

		if (!($flags & static::PRETTY)) {
			return $json;
		}

		foreach (Strings::matchAll($json, static::Pattern) as $hash) {
			$json = str_replace($hash[0], base64_decode($hash[2]), $json);
		}

		return $json;
	}


	/**
	 * @throws JsonException
	 */
	public static function decode(string $json, int $flags = 0): mixed
	{
		return NetteJson::decode($json, $flags);
	}


	/**
	 * @throws IOException
	 * @throws JsonException
	 */
	public static function decodeFile(string $file, int $flags = 0): mixed
	{
		if (!is_file($file)) {
			throw new IOException("File '$file' does not exist.");
		}

		if (($json = @file_get_contents($file)) === false) {
			throw new IOException("File '$file' could not be read.");
		}

		return NetteJson::decode($json, $flags);
	}
}
