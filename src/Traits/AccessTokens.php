<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Arrays;
use JuniWalk\Utils\Interfaces\TokenProvider;	// ! Used for @phpstan
use JuniWalk\Utils\Exceptions\PermissionDeniedException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\Link;
use Nette\Application\UI\InvalidLinkException;
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


	public function injectAccessTokens(
		Storage $storage,
		LinkGenerator $linkGenerator,
	): void {
		$this->cache = new Cache($storage, 'Authorization.Tokens');
		$this->linkGenerator = $linkGenerator;
	}


	/**
	 * @throws PermissionDeniedException
	 */
	public function validateToken(string $token, bool $throw = true): bool
	{
		if ($token === $this->getToken() && $this->getPackage($token, false)) {
			return true;
		}

		if ($throw === false) {
			return false;
		}

		throw new PermissionDeniedException('Token "'.$token.'" is not valid.');
	}


	/**
	 * @param  TokenArgs $args
	 * @throws InvalidLinkException
	 */
	public function createToken(string|Link $dest, array $args = [], ?Identity $identity = null, string $lifespan = '20 minutes'): string
	{
		$token = (string) Uuid::uuid4();

		if ($dest instanceof Link) {
			$args = $dest->getParameters();		// ? Merge with incoming args?
			$dest = $dest->getDestination();
		}

		$dest = ltrim($dest, '/');
		$args['token'] = $token;

		if ($identity instanceof Identity) {
			$args['_identity'] = $identity->getId();
		}

		$request = $this->linkGenerator->createRequest($this, $dest, $args, 'link');

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


	public function clearToken(?string $token = null): void
	{
		if (!$token ??= $this->getToken()) {
			return;
		}

		$this->cache->remove($token);
	}


	/**
	 * @return TokenArgs
	 */
	private function getPackage(string $token, bool $singleUseToken = true): array|false
	{
		/** @var TokenArgs */
		$package = $this->cache->load($token);
		$request = $this->getRequest()?->toArray();

		// ? Store identity aside so it does not interfere
		$identity = $package['_identity'] ?? null;
		unset($package['_identity']);

		// ? Check if the package coresponds to current request (presenter, action, id, etc.)
		if (!$package || Arrays::differenceRecursiveAssoc($package, $request ?? [])) {
			return false;
		}

		if ($singleUseToken) {
			$this->clearToken($token);
		}

		$package['_identity'] = $identity;
		return $package;
	}
}
