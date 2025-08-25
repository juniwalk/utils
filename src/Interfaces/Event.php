<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Interfaces;

/**
 * @template Target of object
 */
interface Event
{
	/**
	 * @param Target $target
	 */
	public function __construct(string $type, object $target);

	public function getType(): string;

	/**
	 * @return Target
	 */
	public function getTarget(): object;

	public function stopPropagation(): void;

	public function isPropagationStopped(): bool;
}
