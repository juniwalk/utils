<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Nette\Utils\Strings as NetteStrings;

final class Strings extends NetteStrings
{
	/**
	 * @param  string  $content
	 * @param  string|null  $lang
	 * @return string
	 */
	public static function slugify(string $content, string $lang = null): string
	{
		$content = self::transliterate($content, $lang);
		$content = self::webalize($content, "'");
		return str_replace("'", '', $content);
	}


	/**
	 * @param  string  $string
	 * @param  string|null  $lang
	 * @return string
	 */
	private static function transliterate(string $string, string $lang = null): string
	{
		switch($lang) {
			case 'ru':
				return transliterator_transliterate('Russian-Latin/BGN', $string);
			break;
		}

		return $string;
	}
}