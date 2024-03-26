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

class Group extends UIControl implements Action
{
	use Actions, Control;

	public function __construct(
		private string $name,
	) {
		$this->control = Html::el('div class="btn-group" role="group"');
		$this->name = Strings::webalize($name);

		$this->setParent(null, $this->name);
	}


	public function create(): Html
	{
		$group = clone $this->control;

		foreach ($this->getActions() as $action) {
			$element = clone $action->create();
			$group->addHtml($element);
		}

		return $group;
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

		if (!$child instanceof Divider) {
			return;
		}

		throw new InvalidStateException($child::class.' is not allowed inside '.$this::class);
	}
}
