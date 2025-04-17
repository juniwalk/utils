<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Attributes\AllowTokenAuthorization;
use JuniWalk\Utils\Interfaces\TokenProvider;	// ! Used for @phpstan
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Security\IIdentity as Identity;
use Ramsey\Uuid\Uuid;

/**
 * @phpstan-require-implements TokenProvider
 * @phpstan-import-type TokenArgs from TokenProvider
 */
trait AccessTokens
{
	private Cache $cache;

	public function injectStorage(Storage $storage): void
	{
		$this->cache = new Cache($storage, 'Authorization.Tokens');
	}


	/**
	 * @param TokenArgs $args
	 */
	public function createToken(string $dest, array $args = [], ?Identity $identity = null, string $lifespan = '20 minutes'): string
	{
		$token = (string) Uuid::uuid4();
		$args += ['token' => $token];

		if ($identity instanceof Identity) {
			$args['_identity'] = $identity->getId();
		}

		$request = $this->getLinkGenerator()->createRequest(
			component: $this,
			destination: $dest,
			args: $args,
			mode: 'link',
		);

		$this->cache->save($token, $request->toArray(), [
			Cache::Expire => $lifespan,
		]);

		return $token;
	}


	public function getToken(): ?string
	{
		$token = $this->getParameter('token') ?? null;

		if (!is_string($token) || !Uuid::isValid($token)) {
			return null;
		}

		return $token;
	}


	public function clearToken(): void
	{
		if (!$token = $this->getToken()) {
			return;
		}

		$this->cache->remove($token);
	}


	/**
	 * @return TokenArgs
	 */
	private function getPackage(string $token, AllowTokenAuthorization $attribute): array
	{
		/** @var TokenArgs */
		$package = $this->cache->load($token);
		$request = $this->getRequest()?->toArray();

		// ? Store identity aside so it does not interfere
		$identity = $package['_identity'] ?? null;
		unset($package['_identity']);

		// ? Check if the package coresponds to current request (presenter, action, id, etc.)
		if (!$package || array_diff_assoc($package, $request ?? [])) {
			return [];
		}

		if ($attribute->singleUseToken) {
			$this->clearToken();
		}

		$package['_identity'] = $identity;
		return $package;
	}
}
