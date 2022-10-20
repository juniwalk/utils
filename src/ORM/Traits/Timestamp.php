<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{
	#[ORM\Column(type: 'datetimetz')]
	private DateTime $created;

	#[ORM\Column(type: 'datetimetz', nullable: true)]
	private ?DateTime $modified = null;


	public function getCreated(): DateTime
	{
		return clone $this->created;
	}


	public function setModified(?DateTime $modified): void
	{
		$this->modified = $modified ? clone $modified : new DateTime;
	}


	public function getModified(): ?DateTime
	{
		if (!$this->modified) {
			return null;
		}

		return clone $this->modified;
	}


	public function getTimestamp(): DateTime
	{
		return clone ($this->modified ?: $this->created);
	}


	#[ORM\PreUpdate]
	public function onUpdate(): void
	{
		$this->modified = new DateTime;
	}


	#[ORM\PrePersist]
	public function onPersist(): void
	{
		$this->created = new DateTime;
	}
}
