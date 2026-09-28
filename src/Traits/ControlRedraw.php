<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use Nette\Application\UI\Renderable;

/**
 * @phpstan-require-implements Renderable
 */
trait ControlRedraw
{
	/**
	 * ? Forces control or its snippet to repaint.
	 */
	public function redrawControl(?string $snippet = null, bool $redraw = true): void
	{
		if (!$this instanceof Renderable) {
			return;
		}

		if ($snippet && $component = $this->getComponent($snippet, false)) {
			$component->redrawControl(null, $redraw);

		} else {
			parent::redrawControl($snippet, $redraw);
		}
	}
}
