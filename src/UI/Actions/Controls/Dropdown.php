<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Actions\Controls;

use JuniWalk\Utils\Html;
use JuniWalk\Utils\Strings;
use JuniWalk\Utils\UI\Actions\Action;
use JuniWalk\Utils\UI\Actions\Traits\Actions;
use JuniWalk\Utils\UI\Actions\Traits\Control;
use Nette\Application\UI\Control as UIControl;
use Nette\ComponentModel\IComponent;
use Nette\InvalidStateException;
use Stringable;

class Dropdown extends UIControl implements Action
{
	use Actions, Control;

	private Html $menu;

	public function __construct(
		private string $name,
		Stringable|string|null $label = null,
	) {
		$this->control = $this->addAction(new Button('_btn', $label, '#'));
		$this->menu = Html::el('div');

		$this->name = Strings::webalize($name);
		$this->setParent(null, $this->name);
	}


	public function addMenuClass($class): static
	{
		$this->menu->addClass($class);
		return $this;
	}


	public function create(): Html
	{
		$button = $this->getControl()->addClass('dropdown-toggle')
			->data('toggle', 'dropdown');

		$this->menu->addClass('dropdown-menu');

		foreach ($this->getActions() as $action) {
			if ($action === $this->control) {
				continue;
			}

			$element = clone $action->create();

			if ($action instanceof Button) {
				$element->setClass('dropdown-item');
			}

			if ($action->hasClass('ajax')) {
				$element->addClass('ajax');
			}

			$this->menu->addHtml($element);
		}

		return Html::el('div class="btn-group" role="group"')
			->addHtml($button)->addHtml($this->menu);
	}


	public function render(): void
	{
		echo $this->create()->render();
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function validateChildComponent(IComponent $child): void
	{
		parent::validateChildComponent($child);

		if (!$child instanceof Group) {
			return;
		}

		throw new InvalidStateException($child::class.' is not allowed inside '.$this::class);
	}
}
