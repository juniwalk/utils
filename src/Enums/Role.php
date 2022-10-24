<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use Nette\Security\Role as IRole;

enum Role: string implements IRole, LabeledEnum
{
	use Traits\Labeled;

	case Guest = 'guest';
	case User = 'user';
	case Manager = 'manager';
	case Admin = 'admin';


	/**
	 * @return string[]
	 */
	public static function getMap(): array
	{
		return [
			self::Guest->value => null,
			self::User->value => self::Guest->value,
			self::Manager->value => self::User->value,
			self::Admin->value => self::Manager->value,
		];
	}


	public function label(): string
	{
		return match($this) {
			self::Guest => 'web.enum.role.guest',
			self::User => 'web.enum.role.user',
			self::Manager => 'web.enum.role.manager',
			self::Admin => 'web.enum.role.admin',
		};
	}


	public function color(): Color
	{
		return match($this) {
			self::Guest => Color::Secondary,
			self::User => Color::Success,
			self::Manager => Color::Primary,
			self::Admin => Color::Warning,
		};
	}


	public function getRoleId(): string
	{
		return $this->value;
	}
}
