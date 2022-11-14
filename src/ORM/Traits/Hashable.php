<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;
use Stringable;

trait Hashable
{
	#[ORM\Column(type: 'string', length: 8, nullable: true)]
	private ?string $hash = null;


	public function getHash(): string
	{
		return $this->hash ?: $this->createUniqueHash();
	}


	/**
	 * @throws InvalidArgumentException
	 */
	protected function createUniqueHash(): string
	{
		if (!$this instanceof Stringable) {
			throw new InvalidArgumentException('Entity has to implement Stringable or use custom createUniqueHash method');
		}

		return substr(sha1((string) $this), 0, 8);
	}
}
