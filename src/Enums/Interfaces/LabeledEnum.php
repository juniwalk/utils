<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums\Interfaces;

use BackedEnum;
use JuniWalk\Utils\Enums\Color;

interface LabeledEnum extends BackedEnum
{
	public static function getLabels(): iterable;

	public function label(): string;
	public function color(): ?Color;
	public function icon(): ?string;
}
