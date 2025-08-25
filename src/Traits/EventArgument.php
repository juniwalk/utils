<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Interfaces\Event;

/**
 * @phpstan-require-implements Event
 */
trait EventArgument
{
	private bool $stopPropagation = false;

	public function __construct(
		public readonly string $type,
		public readonly object $target,
	) {
	}


	public function getType(): string
	{
		return $this->type;
	}


	public function getTarget(): object
	{
		return $this->target;
	}


	public function stopPropagation(): void
	{
		$this->stopPropagation = true;
	}


	public function isPropagationStopped(): bool
	{
		return $this->stopPropagation;
	}
}
