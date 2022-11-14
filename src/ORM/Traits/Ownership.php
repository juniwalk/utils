<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\Utils\ORM\User;

trait Ownership
{
	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
	private User $owner;


	public function setOwner(User $owner): void
	{
		$this->owner = $owner;
	}


	public function getOwner(): User
	{
		return $this->owner;
	}


	public function isOwner(User $owner): bool
	{
		return $this->owner->getId() === $owner->getId();
	}
}
