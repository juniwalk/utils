<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Interfaces;

interface EventHandler
{
	public function when(string $event, callable $callback, ?int $priority = null): void;
	public function isWatched(string $event): bool;
}
