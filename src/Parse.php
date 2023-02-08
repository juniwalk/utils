<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use ValueError;

final class Parse
{
	private const Arguments = '(?:,?\s(?<args>(?:(?:(?:[a-z][a-z0-9]*)\s?(?::|=>?)\s?)?(?:[^&,}]+)(?:(?:&|,)\s?)?)+))?';
	private const HelpKeyword = [
		'name' => '(?<name>(?:(?:[a-z][a-z0-9]+).?)+[^.,\s]+)',
		'args' => self::Arguments,
	];
	private const HelpControl = [
		'name' => '(?<name>[a-z][a-z0-9]+)',
		'type' => '(?::(?<type>[a-z][a-z0-9]+))?',
		'args' => self::Arguments,
	];
	private const HelpLink = [
		'link' => '(?<link>(?:\/\/)?+(?:[^!?#,\s]++)(?:!)?+(?<query>\?[^#]*)?+(?:\#[^\s]*)?+)',
		'args' => self::Arguments,
	];


	/**
	 * @throws ValueError
	 */
	public static function keyword(string $value): ?object
	{
		if (!$match = Strings::match($value, '/^'.implode('', static::HelpKeyword).'$/i')) {
			throw new ValueError('Unable to parse keyword from: '.$value);
		}

		$match['args'] = static::arguments($match['args'] ?? '');

		return (object) $match;
	}


	/**
	 * @throws ValueError
	 */
	public static function control(string $value): ?object
	{
		if (!$match = Strings::match($value, '/^'.implode('', static::HelpControl).'$/i')) {
			throw new ValueError('Unable to parse control from: '.$value);
		}

		$match['args'] = static::arguments($match['args'] ?? '');
		$match['type'] ??= null;

		return (object) $match;
	}


	/**
	 * @throws ValueError
	 */
	public static function link(string $value): ?object
	{
		if (!$match = Strings::match($value, '/^'.implode('', static::HelpLink).'$/i')) {
			throw new ValueError('Unable to parse link from: '.$link);
		}

		try {
			$parts = Presenter::parseDestination($match['link']);
			$parts['path'] = str_replace($match['query'] ?? '', '', $match['link']);
			$parts['args'] = array_merge(
				static::arguments($match['args'] ?? ''),
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
