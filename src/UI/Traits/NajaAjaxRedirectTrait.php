<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Traits;

use Nette\Application\AbortException;

trait NajaAjaxRedirectTrait
{
	private bool $forceRedirect = false;

	/**
	 * @throws AbortException
	 */
	public function redirect(string $dest, mixed ...$args): never
	{
		unset($this->payload->postGet);
		unset($this->payload->url);

		if (!$this->isAjax() || $this->forceRedirect) {
			parent::redirect($dest, ...$args);
		}

		$this->payload->postGet = true;
		$this->payload->url = $this->link($dest, ...$args);

		$this->sendTemplate();
	}


	public function forceRedirect(): static
	{
		$this->forceRedirect = true;
		return $this;
	}


	/**
	 * @deprecated
	 */
	public function redirectAjax(string $dest, mixed ...$args): never
	{
		// trigger_error('RedirectAjax is deprecated, use redirect method directly', E_USER_DEPRECATED);
		$this->redirect($dest, ...$args);
	}
}
