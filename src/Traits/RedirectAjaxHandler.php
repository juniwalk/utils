<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use Nette\Application\AbortException;

trait RedirectAjaxHandler
{
	private bool $forceRedirect = false;

	/**
	 * @throws AbortException
	 */
	public function redirect(string $dest, mixed ...$args): void
	{
		$presenter = $this->getPresenter();

		if (!$presenter->isAjax() || $this->forceRedirect) {
			parent::redirect($dest, ...$args);
		}

		$payload = $presenter->getPayload();
		$payload->url = $this->link($dest, ...$args);
		$payload->postGet = true;
	}


	public function forceRedirect(): static
	{
		$this->forceRedirect = true;
		return $this;
	}
}
