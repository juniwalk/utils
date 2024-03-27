<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

enum Case: string
{
	case Camel = 'camel';
	case Snake = 'snake';
	case Pascal = 'pascal';


	/**
	 * @example snake_case
	 * @example camelCase
	 * @example PascalCase
	 */
	public function format(string $value): string
	{
		return match ($this) {
			self::Camel => lcfirst(self::Pascal->format($value)),
			self::Snake => strtolower(preg_replace('/[A-Z]/', '_$0', $value)),
			self::Pascal => implode('', array_map('ucfirst', preg_split('/[_-]/', $value))),
		}
	}
}
