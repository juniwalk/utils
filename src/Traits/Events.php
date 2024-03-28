<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use Nette\InvalidStateException;

trait Events
{
	private array $events = [];


	/**
	 * @deprecated
	 * @throws InvalidStateException
	 */
	public function on(string $event, callable $callback, ?int $priority = null): void
	{
		trigger_error('Method '.Events::class.'.::on is deprecated, use when', E_USER_DEPRECATED);
		$this->when($event, $callback, $priority);
	}


	/**
	 * @throws InvalidStateException
	 */
	public function when(string $event, callable $callback, ?int $priority = null): void
	{
		$priority ??= sizeof($this->events[$event] ?? []);

		if (!$this->isWatched($event)) {
			throw new InvalidStateException('Event "'.$event.'" is not watched. Call '.static::class.'::watch($event) method.');
		}

		array_splice(
			$this->events[$event],
			$priority, 0,
			[$callback],
		);
	}


	public function isWatched(string $event): bool
	{
		return isset($this->events[$event]);
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function watch(string $event, bool $clear = false): void
	{
		if (!$clear && $this->isWatched($event)) {
			throw new InvalidStateException('Event "'.$event.'" is already watched. Use $clear to re-register.');
		}

		$this->events[$event] = [];
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function trigger(string $event, mixed ...$args): void
	{
		if (!$this->isWatched($event)) {
			throw new InvalidStateException('Event "'.$event.'" is not watched. Call '.static::class.'::listen($event) method.');
		}

		ksort($this->events[$event], SORT_NUMERIC);

		foreach ($this->events[$event] as $event) {
			call_user_func($event, ...$args);
		}
	}
}
