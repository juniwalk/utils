<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Nette\Utils\Strings as NetteStrings;
use Latte\Essential\Filters as LatteFilters;
use Latte\Runtime\FilterInfo;
use Stringable;

final class Strings extends NetteStrings
{
	public static function stripHtml(Stringable|string $content): string
	{
		return LatteFilters::stripHtml(new FilterInfo('html'), $content);
	}


	public static function slugify(string $content, ?string $lang = null): string
	{
		$content = self::transliterate($content, $lang);
		$content = self::webalize($content, "'");
		return str_replace("'", '', $content);
	}


	public static function shorten(string $content, int $length = 6, string $token = '…'): string
	{
		if (static::length($content) < $length * 2) {
			return $content;
		}

		$start = static::substring($content, 0, $length);
		$end = static::substring($content, $length * -1);

		return $start.$token.$end;
	}


	private static function transliterate(string $string, ?string $lang = null): string
	{
		return (string) match ($lang) {
			'ru' => transliterator_transliterate('Russian-Latin/BGN', $string),
			default => $string,
		};
	}
}
