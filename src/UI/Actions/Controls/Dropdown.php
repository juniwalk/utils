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

	public function __construct(
		private string $name,
		Stringable|string $label = null,
	) {
		$this->control = $this->addAction(new Button('_btn', $label, '#'));
		$this->name = Strings::webalize($name);

		$this->setParent(null, $this->name);
	}


	public function create(): Html
	{
		$dropdownMenu = Html::el('div class="dropdown-menu"');
		$button = $this->getControl()->addClass('dropdown-toggle')
			->data('toggle', 'dropdown');

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

			$dropdownMenu->addHtml($element);
		}

		return Html::el('div class="btn-group" role="group"')
			->addHtml($button)->addHtml($dropdownMenu);
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
