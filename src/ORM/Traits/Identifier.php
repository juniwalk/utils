<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Identifier
{
	#[ORM\Column(type: 'integer', unique: true, nullable: false)]
	#[ORM\GeneratedValue(strategy: 'IDENTITY')]
	#[ORM\Id]
	protected int $id;


	final public function getId(): ?int
	{
		return $this->id ?? null;
	}


	public function __clone()
	{
		unset($this->id);
	}
}
