<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Interfaces\EventAutoWatch;
use JuniWalk\Utils\Interfaces\EventHandler;	// ! Used for @phpstan
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

		// Pre-sort current events before returning array reference
		ksort($this->events[$event], SORT_NUMERIC);

		return $this->events[$event];
	}


	/**
	 * @throws InvalidArgumentException
	 */
	public function __isset(string $name): bool
	{
		if (!str_starts_with($name, 'on')) {
			throw new InvalidArgumentException('Event name should use format on[EventName], '.$name.' given.');
		}

		$event = Format::kebabCase($name);
		$event = substr($event, 3);

		return isset($this->events[$event]);
	}


	/**
	 * @throws InvalidStateException
	 */
	public function isWatched(string $event, bool $require = false): bool
	{
		$event = Format::kebabCase($event);
		$isWatched = isset($this->events[$event]);

		if (!$isWatched && $this instanceof EventAutoWatch) {
			$this->watch($event, true);
			return true;
		}

		if (!$isWatched && $require) {
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
	 * @param  string|string[] $events
	 * @param  EventCallback $callback
	 * @throws InvalidStateException
	 */
	public function whenAny(string|array $events, callable $callback): void
	{
		if (is_string($events) && !str_contains($events, ',')) {
			throw new InvalidStateException('Events need to be comma separated, "'.$events.'" given.');
		}

		if (is_string($events)) {
			$events = explode(',', $events);
		}

		foreach ($events as $event) {
			$this->when($event, $callback);
		}
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


	protected function trigger(string $event, mixed ...$args): void
	{
		$event = Format::kebabCase($event);

		if (empty($this->events[$event] ?? [])) {
			return;
		}

		ksort($this->events[$event], SORT_NUMERIC);

		foreach ($this->events[$event] as $handler) {
			// TODO: Send $this as first argument by default
			// TODO: Send EventArgument with $target($this) and $event(name) as last argument
			call_user_func($handler, ...$args);
		}
	}
}
