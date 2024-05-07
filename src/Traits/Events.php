<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Interfaces\EventAutoWatch;
use JuniWalk\Utils\Interfaces\EventHandler;
use JuniWalk\Utils\Format;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;

/**
 * @phpstan-require-implements EventHandler
 * @phpstan-type EventCallback callable
 */
trait Events
{
	/** @var array<string, EventCallback[]> */
	private array $events = [];


	/**
	 * @return EventCallback[]
	 * @throws InvalidArgumentException
	 */
	public function &__get(string $name): array
	{
		if (!str_starts_with($name, 'on')) {
			throw new InvalidArgumentException('Event name should use format on[EventName], '.$name.' given.');
		}

		$event = Format::kebabCase($name);
		$event = substr($event, 3);

		$this->isWatched($event, true);
		return $this->events[$event];
	}


	/**
	 * @throws InvalidStateException
	 */
	public function isWatched(string $event, bool $throw = false): bool
	{
		$isWatched = isset($this->events[$event]);
		$event = Format::kebabCase($event);

		if (!$isWatched && $this instanceof EventAutoWatch) {
			$this->watch($event, true);
			return true;
		}

		if (!$isWatched && $throw) {
			throw new InvalidStateException('Event "'.$event.'" is not being watched.');
		}

		return $isWatched;
	}


	/**
	 * @param EventCallback $callback
	 */
	public function when(string $event, callable $callback, ?int $priority = null): void
	{
		$event = Format::kebabCase($event);
		$this->isWatched($event, true);

		$priority ??= sizeof($this->events[$event] ?? []);

		array_splice(
			$this->events[$event],
			$priority, 0,
			[$callback],
		);
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function watch(string $event, bool $clear = false): static
	{
		$event = Format::kebabCase($event);

		if (!$clear && $this->isWatched($event) && !$this instanceof EventAutoWatch) {
			throw new InvalidStateException('Event "'.$event.'" is already watched. Use $clear to re-register.');
		}

		$this->events[$event] = [];
		return $this;
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function unwatch(string $event): static
	{
		$event = Format::kebabCase($event);
		$this->isWatched($event, true);

		unset($this->events[$event]);
		return $this;
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function trigger(string $event, mixed ...$args): void
	{
		$event = Format::kebabCase($event);
		$this->isWatched($event, true);

		ksort($this->events[$event], SORT_NUMERIC);

		foreach ($this->events[$event] as $event) {
			call_user_func($event, ...$args);
		}
	}
}
