<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use Nette\Application\AbortException;

trait NajaAjaxRedirectTrait
{
	private bool $forceRedirect = false;

	/**
	 * @throws AbortException
	 */
	public function redirect(string $dest, mixed ...$args): void
	{
		$presenter = $this->getPresenter();
		$payload = $presenter->getPayload();

		unset($payload->postGet);
		unset($payload->url);

		if (!$presenter->isAjax() || $this->forceRedirect) {
			parent::redirect($dest, ...$args);
		}

		$payload->url = $this->link($dest, ...$args);
		$payload->postGet = true;
	}


	public function forceRedirect(): static
	{
		$this->forceRedirect = true;
		return $this;
	}


	/**
	 * @deprecated
	 */
	public function redirectAjax(string $dest, mixed ...$args): void
	{
		// trigger_error('RedirectAjax is deprecated, use redirect method directly', E_USER_DEPRECATED);
		$this->redirect($dest, ...$args);
	}
}
