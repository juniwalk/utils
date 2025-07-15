<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use JuniWalk\Utils\Enums\Interfaces\PoweredRole;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;

enum Role: string implements PoweredRole, LabeledEnum
{
	use Traits\Labeled;

	case Guest = 'guest';
	case Client = 'client';
	case User = 'user';
	case Manager = 'manager';
	case Admin = 'admin';


	/**
	 * @return array<string, string|null>
	 */
	public static function getMap(): array
	{
		return [
			self::Guest->value => null,
			self::Client->value => self::Guest->value,
			self::User->value => self::Client->value,
			self::Manager->value => self::User->value,
			self::Admin->value => self::Manager->value,
		];
	}


	public function label(): string
	{
		return match ($this) {
			self::Guest => 'enum.role.guest',
			self::Client => 'enum.role.client',
			self::User => 'enum.role.user',
			self::Manager => 'enum.role.manager',
			self::Admin => 'enum.role.admin',
		};
	}


	public function color(): Color
	{
		return match ($this) {
			self::Guest => Color::Secondary,
			self::Client => Color::Info,
			self::User => Color::Success,
			self::Manager => Color::Primary,
			self::Admin => Color::Warning,
		};
	}


	public function power(): int
	{
		return match ($this) {
			self::Guest => 0,
			self::Client => 0,
			self::User => 1,
			self::Manager => 2,
			self::Admin => 3,
		};
	}


	public function hasPowerOver(PoweredRole $role): bool
	{
		if ($this === Role::Admin) {
			return true;
		}

		return $this->power() >= $role->power();
	}


	public function getRoleId(): string
	{
		return $this->value;
	}
}
