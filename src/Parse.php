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


	public static function luminosity(string $hex): float
	{
		$mod = [0.2126, 0.7152, 0.0722];
		$hex = Strings::split($hex, '/\#?([a-f0-9]{2})/', PREG_SPLIT_NO_EMPTY);
		$rgb = Arrays::map($hex, fn(string $v, int $k): float => (hexdec($v) / 255) * $mod[$k]);
		return array_sum($rgb);
	}


	/**
	 * @return object{name: string, args: array<string, mixed>}
	 * @throws ValueError
	 */
	public static function keyword(string $value): object
	{
		if (!$match = Strings::match($value, '/^'.implode('', static::HelpKeyword).'$/i')) {
			throw new ValueError('Unable to parse keyword from: '.$value);
		}

		$match['args'] = static::arguments($match['args'] ?? '');

		return (object) $match;
	}


	/**
	 * @return object{name: string, type: ?string, args: array<string, mixed>}
	 * @throws ValueError
	 */
	public static function control(string $value): object
	{
		if (!$match = Strings::match($value, '/^'.implode('', static::HelpControl).'$/i')) {
			throw new ValueError('Unable to parse control from: '.$value);
		}

		$match['args'] = static::arguments($match['args'] ?? '');
		$match['type'] ??= null;

		return (object) $match;
	}


	/**
	 * @return object{path: string, absolute: bool, signal: bool, args: array<string, mixed>, fragment: string}
	 * @throws ValueError
	 */
	public static function link(string $value): ?object
	{
		if (!$match = Strings::match($value, '/^'.implode('', static::HelpLink).'$/i')) {
			throw new ValueError('Unable to parse link from: '.$value);
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


	/**
	 * @return array<string, mixed>
	 */
	public static function arguments(string $args): array
	{
		if (empty($args)) {
			return [];
		}

		$args = Strings::split($args, '/[,]\s*/');
		$args = Arrays::walk($args, function(string $arg) {
			$pair = Strings::split($arg, '/\s*(?::|=>?)\s*/');
			$pair = array_pad($pair, -2, 0);

			yield $pair[0] => match (Strings::lower((string) $pair[1])) {
				'false' => false,
				'true' => true,
				'null' => null,

				default => $pair[1],
			};
		});

		return $args;
	}


	public static function number(mixed $value): ?string
	{
		$value = Format::stringify($value);
		$value = trim($value);

		if ($value === '') {
			return null;
		}

		preg_match_all('/[+\-]?(?:\d[\d\h.,\x{00A0}\x{202F}]*\d|\d)(?:[eE][+\-]?\d+)?/u', $value, $matches);
		$numbers = array_values(array_filter(
			$matches[0],
			static fn(string $number): bool => preg_match('/\d/', $number) === 1,
		));

		if (count($numbers) !== 1) {
			return null;
		}

		return static::numberNormalize($numbers[0]);
	}


	private static function numberNormalize(string $value): ?string
	{
		if (!($match = Strings::match($value, '/^(.+?)([eE][+\-]?\d+)?$/'))) {
			return null;
		}

		$base = preg_replace('/[\h\x{00A0}\x{202F}]/u', '', $match[1]);

		if (is_null($base)) {
			return null;
		}

		$exponent = $match[2] ?? '';

		if ($base === '') {
			return null;
		}

		$comma = strrpos($base, ',');
		$dot = strrpos($base, '.');

		if ($comma !== false && $dot !== false) {
			$decimal = $comma > $dot ? ',' : '.';
			$grouping = $decimal === ',' ? '.' : ',';

			$base = str_replace($grouping, '', $base);
			$base = str_replace($decimal, '.', $base);

		} elseif ($comma !== false) {
			$base = static::numberSeparatorNormalize($base, ',');

		} elseif (substr_count($base, '.') > 1) {
			$base = static::numberSeparatorNormalize($base, '.');
		}

		if (is_null($base) || preg_match('/^[+\-]?(?:\d+|\d*\.\d+)$/', $base) !== 1) {
			return null;
		}

		return $base.$exponent;
	}


	private static function numberSeparatorNormalize(string $value, string $separator): ?string
	{
		$separatorRegex = preg_quote($separator, '/');
		$isGrouping = substr_count($value, $separator) > 1
			&& preg_match('/^[+\-]?\d{1,3}(?:'.$separatorRegex.'\d{3})+$/', $value) === 1;

		if ($isGrouping) {
			return str_replace($separator, '', $value);
		}

		if (substr_count($value, $separator) > 1) {
			return null;
		}

		return $separator === ',' ? str_replace(',', '.', $value) : $value;
	}
}
