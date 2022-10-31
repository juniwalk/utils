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

final class Json implements Stringable
{
	private const PATTERN = '/(\"code\:([\/=+0-9a-z]+)\")/iU';
	private const LITERAL = 'code:';

	public const FORCE_ARRAY = NetteJson::FORCE_ARRAY;
	public const PRETTY = NetteJson::PRETTY;
	public const ESCAPE_UNICODE = NetteJson::ESCAPE_UNICODE;


	private function __construct(
		private readonly string $code
	) {}


	public function __toString(): string
	{
		return static::LITERAL.base64_encode($this->code);
	}


	public static function literal(string $code): static
	{
		return new static($code);
	}


	/**
	 * @throws JsonException
	 */
	public static function encode(mixed $content, int $flags = 0): string
	{
		if (!is_iterable($content)) {
			return NetteJson::encode($content, $flags);
		}

		$content = Arrays::map($content, function(mixed $value): mixed {
			if (!$value instanceof Stringable) {
				return $value;
			}

			return (string) $value;
		});

		$json = NetteJson::encode($content, $flags);

		if (!($flags & static::PRETTY)) {
			return $json;
		}

		foreach (Strings::matchAll($json, static::PATTERN) as $hash) {
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

		$json = file_get_contents($file);
		return NetteJson::decode($json, $flags);
	}
}
