<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Interfaces;

use Nette\Application\IPresenter;
use Nette\Security\IIdentity as Identity;

/**
 * @phpstan-type TokenArgs array<string, mixed>
 */
interface TokenProvider extends IPresenter
{
	/**
	 * @param TokenArgs $args
	 */
	public function createToken(string $dest, array $args = [], ?Identity $identity = null, string $lifespan = '20 minutes'): string;
	public function getToken(): ?string;
	public function clearToken(): void;
}
