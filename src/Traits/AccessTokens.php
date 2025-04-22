<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Interfaces\TokenProvider;	// ! Used for @phpstan
use Nette\Application\LinkGenerator;
use Nette\Application\UI\Link;
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
	private LinkGenerator $linkGenerator;
	private Cache $cache;

	public function injectStorage(Storage $storage): void
	{
		$this->cache = new Cache($storage, 'Authorization.Tokens');
	}

	public function injectLinkGenerator(LinkGenerator $linkGenerator): void
	{
		$this->linkGenerator = $linkGenerator;
	}


	/**
	 * @param TokenArgs $params
	 */
	public function createToken(string|Link $dest, array $params = [], ?Identity $identity = null, string $lifespan = '20 minutes'): string
	{
		if ($dest instanceof Link) {
			$params = $dest->getParameters();
			$dest = $dest->getDestination();
		}

		$token = (string) Uuid::uuid4();
		$params += ['token' => $token];

		if ($identity instanceof Identity) {
			$params['_identity'] = $identity->getId();
		}

		$request = $this->linkGenerator->createRequest(
			component: $this,
			destination: $dest,
			args: $params,
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
	private function getPackage(string $token, bool $singleUseToken = true): array
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

		if ($singleUseToken) {
			$this->clearToken();
		}

		$package['_identity'] = $identity;
		return $package;
	}
}
