<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Actions\Controls;

use JuniWalk\Utils\Html;
use JuniWalk\Utils\Strings;
use JuniWalk\Utils\UI\Actions\Action;
use JuniWalk\Utils\UI\Actions\Component;
use JuniWalk\Utils\UI\Actions\Traits\Control;
use Nette\Application\UI\Control as UIControl;
use Nette\Application\UI\Link;

class Button extends UIControl implements Action, Component
{
	use Control;

	private Link|string $link;

	public function __construct(
		private string $name,
		private ?string $label = null,
	) {
		$this->name = Strings::webalize($name);
		$this->control = Html::el('a');

		$this->setParent(null, $this->name);
	}


	public function setLink(Link|string $link): static
	{
		$this->link = $link;
		return $this;
	}


	public function create(): Html
	{
		$label = $this->translator?->translate($this->label) ?? $this->label;
		return $this->getControl()->addHtml($label)->setHref($this->link ?? '#');
	}


	public function render(): void
	{
		echo $this->create()->render();
	}
}
