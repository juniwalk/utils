<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Utils;

/**
 * @phpstan-type Point array{0: int, 1: int, 2?: int}
 */
final class Vectors
{
	/**
	 * @param  Point[] $points
	 * @return Point
	 */
	public static function pointsCenter(array $points): array
	{
		if (!$count = count($points)) {
			return [0, 0];
		}

		$x = array_column($points, 0);
		$y = array_column($points, 1);

		return [
			array_sum($x) / $count,
			array_sum($y) / $count,
		];
	}


	/**
	 * @param  Point[] $points
	 * @return Point[]
	 */
	public static function pointsAroundCenter(array $points): array
	{
		$center = static::pointsCenter($points);

		foreach ($points as $key => $point) {
			$points[$key]['angle'] = atan2(
				$point[1] - $center[1],	// distance from center Y
				$point[0] - $center[0],	// distance from center X
			);
		}

		usort($points, static fn($a, $b) => $a['angle'] <=> $b['angle']);
		array_walk($points, static function(&$x) {
			unset($x['angle']);
		});

		return $points;
	}
}
