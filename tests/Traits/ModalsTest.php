<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

use JuniWalk\Utils\Interfaces\EventAutoWatch;
use JuniWalk\Utils\Interfaces\EventHandler;
use JuniWalk\Utils\Traits\Events;
use Nette\InvalidStateException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @property callable[] $onEvent
 */
abstract class EventTest implements EventHandler
{
	use Events;

	public bool $triggered = false;

	public function __construct()
	{
		if ($this instanceof EventAutoWatch) {
			return;
		}

		$this->watch('event');
	}

	public function event(): void
	{
		$this->trigger('event', $this);
	}
}

/**
 * @testCase
 */
final class ModalsTest extends TestCase
{
	public function testTriggerWatched(): void
	{
		$eventTest = new class extends EventTest {};
		Assert::true($eventTest->isWatched('event'));

		$eventTest->when('event', fn($x) => $x->triggered = true);
		$eventTest->event();

		Assert::true($eventTest->triggered);
	}


	public function testTriggerAutoWatched(): void
	{
		$eventTest = new class extends EventTest implements EventAutoWatch {};
		Assert::true($eventTest->isWatched('event'));

		$eventTest->when('event', fn($x) => $x->triggered = true);
		$eventTest->event();

		Assert::true($eventTest->triggered);
	}


	public function testTriggerWatchAutoWatched(): void
	{
		$eventTest = new class extends EventTest implements EventAutoWatch {
			public function __construct() { $this->watch('event'); }
		};

		Assert::true($eventTest->isWatched('event'));

		$eventTest->when('event', fn($x) => $x->triggered = true);
		$eventTest->event();

		Assert::true($eventTest->triggered);
	}


	public function testTriggerUnWatched(): void
	{
		$eventTest = new class extends EventTest {
			public function __construct() {}
		};

		Assert::false($eventTest->isWatched('event'));

		Assert::exception(
			fn() => $eventTest->when('event', fn($x) => $x->triggered = true),
			InvalidStateException::class,
			'Event "%w%" is not being watched.',
		);

		Assert::exception(
			fn() => $eventTest->event(),
			InvalidStateException::class,
			'Event "%w%" is not being watched.',
		);

		Assert::false($eventTest->triggered);
	}


	public function testTriggerWatchedArrayAccess(): void
	{
		$eventTest = new class extends EventTest {};
		Assert::true($eventTest->isWatched('event'));

		$eventTest->onEvent[] = fn($x) => $x->triggered = true;
		$eventTest->event();

		Assert::true($eventTest->triggered);
	}


	public function testTriggerAutoWatchedArrayAccess(): void
	{
		$eventTest = new class extends EventTest implements EventAutoWatch {};
		Assert::true($eventTest->isWatched('event'));

		$eventTest->onEvent[] = fn($x) => $x->triggered = true;
		$eventTest->event();

		Assert::true($eventTest->triggered);
	}
}

(new ModalsTest)->run();
