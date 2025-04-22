<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Enums\Interfaces\PoweredRole;
use JuniWalk\Utils\Exceptions\PermissionDeniedException;
use Nette\Application\IPresenter;	// ! Used for @phpstan
use Nette\InvalidStateException;

/**
 * @phpstan-require-implements IPresenter
 */
trait RequiredAuthorized
{
	protected bool $_isRequiredAuthorized = true;

	public function injectRequireAuthorization(): void
	{
		$this->onStartup[] = $this->forceAuthorized(...);
	}


	/**
	 * @throws PermissionDeniedException
	 */
	private function forceAuthorized(): void
	{
		if (!$this->_isRequiredAuthorized) {
			return;
		}

		$this->isAllowed(
			resource: $this->getName(),
			task: $this->getAction(),
		);
	}


	/**
	 * @throws PermissionDeniedException
	 */
	public function isAllowed(?string $resource, ?string $task, ?PoweredRole $role = null): void
	{
		$user = $this->getUser();

		if (!$resource || !$task) {
			return;
		}

		try {
			if (!$user->isAllowed($resource, $task)) {
				throw PermissionDeniedException::fromTask($resource, $task);
			}

		} catch (InvalidStateException) {
			return;
		}

		foreach ($user->getRoles() as $me) {
			if ($role && !$me->hasPowerOver($role)) {
				continue;
			}

			return;
		}

		throw PermissionDeniedException::fromTask($resource, $task);
	}
}
