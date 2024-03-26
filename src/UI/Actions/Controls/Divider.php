<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Actions\Controls;

use JuniWalk\Utils\Html;
use JuniWalk\Utils\UI\Actions\Action;
use JuniWalk\Utils\UI\Actions\Traits\Control;
use Nette\Application\UI\Control as UIControl;

class Divider extends UIControl implements Action
{
	use Control;

	public function __construct(
		private string $name,
	) {
		$this->control = Html::el('div class="dropdown-divider"');
		$this->setParent(null, $this->name);
	}


	public function create(): Html
	{
		return $this->getControl();
	}


	public function render(): void
	{
		echo $this->create()->render();
	}
}
