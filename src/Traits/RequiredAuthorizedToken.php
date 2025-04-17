<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Attributes\AllowTokenAuthorization;
use Nette\Application\IPresenter;	// ! Used for @phpstan
use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\Presenter;
use Nette\Security\SimpleIdentity;
use ReflectionClass;
use ReflectionMethod;

/**
 * @phpstan-require-implements IPresenter
 */
trait RequiredAuthorizedToken
{
	use AccessTokens;
	use RequiredAuthorized;


	/**
	 * @param ReflectionClass<Presenter> $element
	 */
	public function checkRequirements(ReflectionClass|ReflectionMethod $element): void
	{
		parent::checkRequirements($element);

		$attribute = $this->findAuthorizationAttribute();

		if (!$attribute || !$token = $this->getToken()) {
			return;
		}

		if (!$package = $this->getPackage($token, $attribute)) {
			return;
		}

		$user = $this->getUser();

		if ($userId = $package['_identity'] ?? null) {
			$user->login(new SimpleIdentity($userId));
			$user->refreshStorage();

			// ? Only allow identity access through token to this page
			$this->onShutdown[] = fn() => $user->logout();
		}

		$this->_isRequiredAuthorized = false;
	}


	private function findAuthorizationAttribute(): ?AllowTokenAuthorization
	{
		$action = static::formatActionMethod($this->action);
		$class = new ComponentReflection(static::class);

		$attributes = $class->getAttributes(AllowTokenAuthorization::class);

		if ($class->hasMethod($action)) {
			$method = $class->getMethod($action);
			$attributes = array_merge(
				$method->getAttributes(AllowTokenAuthorization::class),
				$attributes,
			);
		}

		if (empty($attributes)) {
			return null;
		}

		return $attributes[0]->newInstance();
	}
}
