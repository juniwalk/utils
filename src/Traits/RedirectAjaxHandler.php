<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;

trait RedirectAjaxHandler
{
	private bool $forceRedirect = false;

	/**
	 * @throws AbortException
	 */
	public function redirect(string $dest, mixed ...$args): void
	{
		$self = $this->getPresenter();
		$payload = $self->getPayload();

		unset($payload->postGet);
		unset($payload->url);

		if (!$self->isAjax()) {
			parent::redirect($dest, ...$args);
		}

		$payload->url = $this->link($dest, ...$args);
		$payload->postGet = true;
	}


	public function isAjax(): bool
	{
		if (!$this instanceof Presenter) {
			return $this->getPresenter()->isAjax();
		}

		return parent::isAjax() && !$this->forceRedirect;
	}


	public function forceRedirect(): static
	{
		$this->forceRedirect = true;
		return $this;
	}


	/**
	 * @throws AbortException
	 * @deprecated
	 */
	public function redirectAjax(string $dest, mixed ...$args): void
	{
		// trigger_error('Method redirectAjax is deprecated, call redirect directly', E_USER_DEPRECATED);
		$this->redirect($dest, ...$args);
	}
}
