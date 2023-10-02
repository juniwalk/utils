<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Traits;

trait NajaAjaxRedirectTrait
{
	public function redirect(string $dest, mixed ...$args): void
	{
		unset($this->payload->postGet);
		unset($this->payload->url);

		parent::redirect($dest, ...$args);
	}


	public function redirectAjax(string $dest, mixed ...$args): void
	{
		if (!$this->isAjax()) {
			$this->redirect($dest, ...$args);
		}

		$this->payload->postGet = true;
		$this->payload->url = $this->link($dest, ...$args);
	}
}
