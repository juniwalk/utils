<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use Nette\Security\User;

enum SignoutReason
{
	case Unknown;
	case Manual;
	case Inactivity;
	case Banned;


	public function fromUser(User $user): self
	{
		return match ($user->getLogoutReason()) {
			User::LogoutInactivity => self::Inactivity,
			User::LogoutManual => self::Manual,
			default => self::Unknown,
		};
	}
}
