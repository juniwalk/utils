<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

final class Parse
{
	private const NetteLink = [
		'link' => '(?<link>(?:\/\/)?+(?:[^!?#\s]++)(?:!)?+(?<query>\?[^#]*)?+(?:\#[^\s]*)?+)',
		'args' => '(?:,?\s(?<arguments>(?:(?:[a-z0-9]+)\s?(?::|=>?)?\s?(?:[a-z0-9]+)?(?:&|,)?\s?)+)?)?',
	];


	public static function link(string $link): ?object
	{
		try {
			$match = Strings::match($link, '/^'.implode('', static::NetteLink).'$/i');

			$parts = Presenter::parseDestination($match['link']);
			$parts['path'] = str_replace($match['query'], '', $match['link']);
			$parts['args'] = array_merge(
				static::arguments($match['arguments'] ?? ''),
				$parts['args'] ?? [],
			);

		} catch (InvalidLinkException) {
			return null;
		}

		return (object) $parts;
	}


	public static function arguments(string $args): array
	{
		if (empty($args)) {
			return [];
		}

		$args = Strings::split($args, '/[,]\s*/');
		$args = Arrays::map($args, function(mixed $arg): array {
			$pair = Strings::split($arg, '/\s*(?::|=>?)\s*/');
			$pair = array_pad($pair, -2, 0);
			return [$pair[0] => $pair[1]];
		});

		return array_merge(... $args);
	}
}
