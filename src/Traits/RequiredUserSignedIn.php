<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Enums\SignoutReason;
use JuniWalk\Utils\Interfaces\SignInHandler;	// ! Used for @phpstan

/**
 * @phpstan-require-implements SignInHandler
 */
trait RequiredUserSignedIn
{
	protected bool $_isRequiredSignedIn = true;

	public function injectRequireUserSignedIn(): void
	{
		$this->onStartup[] = $this->forceUserSignedIn(...);
	}


	private function forceUserSignedIn(): void
	{
		$user = $this->getUser();

		if (!$this->_isRequiredSignedIn || $user->isLoggedIn()) {
			return;
		}

		$this->forceSignIn(SignoutReason::fromUser($user));
	}
}
