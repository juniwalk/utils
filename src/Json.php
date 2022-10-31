<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Nette\Utils\Json as NetteJson;
use Stringable;

final class Json extends NetteJson
{
	public const PATTERN = '/(\"code\:([\/=+0-9a-z]+)\")/iU';
	public const LITERAL = 'code:';


	/**
	 * @inheritDoc
	 */
	public static function encode(mixed $content, bool $extended = false, bool $escapeUnicode = false): string
	{
		if (!is_iterable($content)) {
			return parent::encode($content, $extended, $escapeUnicode);
		}

		$content = Arrays::map($content, function(mixed $value): mixed {
			if (!$value instanceof Stringable) {
				return $value;
			}

			return (string) $value;
		});

		$json = parent::encode($content, $extended, $escapeUnicode);

		if ($extended === false) {
			return $json;
		}

		foreach (Strings::matchAll($json, static::PATTERN) as $hash) {
			$json = str_replace($hash[0], base64_decode($hash[2]), $json);
		}

		return $json;
	}
}
