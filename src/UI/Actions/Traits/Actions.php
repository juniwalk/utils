<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Actions\Traits;

use JuniWalk\Utils\Strings;
use JuniWalk\Utils\UI\Actions\Action;
use JuniWalk\Utils\UI\Actions\Component;
use JuniWalk\Utils\UI\Actions\LinkProvider;
use JuniWalk\Utils\UI\Actions\Controls\Button;
use JuniWalk\Utils\UI\Actions\Controls\Divider;
use JuniWalk\Utils\UI\Actions\Controls\Dropdown;
use JuniWalk\Utils\UI\Actions\Controls\Group;
use Nette\Application\UI\Presenter;
use Nette\Utils\Random;
use Stringable;

trait Actions
{
	public function addGroup(?string $name = null): Action
	{
		$action = new Group($name ?? Random::generate(6));
		return $this->addAction($action);
	}


	public function addButton(string $name, Stringable|string|null $label = null, string $dest = null, array $args = []): Action
	{
		$action = new Button($name, $label);
		$action->monitor(Presenter::class, fn() => $action->setLink(
			$action->lookup(LinkProvider::class)->createLink($dest ?? $name, $args)
		));

		return $this->addAction($action);
	}


	public function addDropdown(string $name, Stringable|string|null $label = null): Action
	{
		$action = new Dropdown($name, $label);
		return $this->addAction($action);
	}


	public function addDivider(?string $name = null): Action
	{
		$action = new Divider($name ?? Random::generate(6));
		return $this->addAction($action);
	}


	public function addAction(Action $action): Action
	{
		if ($action instanceof Component) {
			$action->monitor(Presenter::class, fn($parent) => $action->setTranslator($parent->getTranslator()));
		}

		$this->addComponent($action, null);
		return $action;
	}


	public function findAction(string $name): ?Action
	{
		return $this->getComponent(Strings::webalize($name), false);
	}


	public function getAction(string $name): Action
	{
		return $this->getComponent(Strings::webalize($name));
	}


	public function getActions(): iterable
	{
		return $this->getComponents(false, Action::class);
	}


	public function removeAction(string $name): ?Action
	{
		if ($action = $this->findAction($name)) {
			$this->removeComponent($action);
		}

		return $action;
	}
}
