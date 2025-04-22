<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\ORM\Entity\Interfaces\Activated;
use JuniWalk\Utils\Enums\SignoutReason;
use JuniWalk\Utils\Interfaces\SignInHandler;	// ! Used for @phpstan

/**
 * @phpstan-require-implements SignInHandler
 */
trait RequiredUserNotBanned
{
	public function injectRequireUserNotBanned(): void
	{
		$this->onStartup[] = $this->forceUserNotBanned(...);
	}


	private function forceUserNotBanned(): void
	{
		$user = $this->getUser()->getIdentity();

		if (!$user instanceof Activated || $user->isActive()) {
			return;
		}

		$this->getUser()->logout(true);
		$this->forceSignIn(SignoutReason::Banned);
	}
}
