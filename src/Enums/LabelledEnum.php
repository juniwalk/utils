<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use BackedEnum;

interface LabelledEnum extends BackedEnum
{
	/**
	 * @return string[]
	 */
	public static function getItems(): iterable;

	/**
	 * @return string
	 */
	public function label(): string;

	/**
	 * @return string|null
	 */
	public function icon(): ?string;

	/**
	 * @return string|null
	 */
	public function color(): ?string;
}
