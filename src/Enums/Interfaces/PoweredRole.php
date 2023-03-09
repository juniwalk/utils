<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums\Interfaces;

use Nette\Security\Role as NetteRole;

interface PoweredRole extends NetteRole
{
	public function hasPowerOver(self $role): bool;
	public function power(): int;
}
