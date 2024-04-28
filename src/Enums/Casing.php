<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

enum Casing: string
{
	case Camel = 'camel';
	case Snake = 'snake';
	case Kebab = 'kebab';
	case Pascal = 'pascal';


	/**
	 * @example camelCase
	 * @example snake_case
	 * @example kebab-case
	 * @example PascalCase
	 */
	public function format(string $value): string
	{
		$value = $this->camelCase($value);

		return match ($this) {
			self::Camel => $value,
			self::Pascal => ucfirst($value),
			self::Kebab => strtolower(preg_replace('/[A-Z]/', '-$0', $value) ?: ''),
			self::Snake => strtolower(preg_replace('/[A-Z]/', '_$0', $value) ?: ''),
		};
	}


	private function camelCase(string $value): string
	{
		if (!$value = preg_split('/[_-]/', $value)) {
			return '';
		}

		return lcfirst(implode('', array_map('ucfirst', $value)));
	}
}
