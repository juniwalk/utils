<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use BackedEnum;

interface LabeledEnum extends BackedEnum
{
	public static function getLabels(): iterable;

	
	public function label(): string;


	public function color(): ?Color;


	public function icon(): ?string;
}
