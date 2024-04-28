<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums\Interfaces;

use BackedEnum;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Html;

interface LabeledEnum extends BackedEnum
{
	public static function make(mixed $value, bool $required = true): ?static;

	/**
	 * @return array<string, string>
	 */
	public static function getLabels(): array;

	/**
	 * @return array<string, Html>
	 */
	public static function getOptions(bool $badge = true): array;

	public function label(): string;
	public function color(): Color;
	public function icon(): ?string;
}
