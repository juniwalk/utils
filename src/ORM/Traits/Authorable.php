<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\Utils\ORM\User;

trait Authorable
{
	#[ORM\ManyToOne(targetEntity: User::class)]
	private ?User $author = null;


	public function setAuthor(?User $author): void
	{
		$this->author = $author;
	}


	public function getAuthor(): ?User
	{
		return $this->author;
	}


	public function isAuthor(?User $author): bool
	{
		if (!$author XOR !$this->author) {
			return false;
		}

		if (!$author && !$this->author) {
			return true;
		}

		return $this->author->getId() === $author->getId();
	}
}
