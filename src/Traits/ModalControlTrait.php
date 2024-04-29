<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use JuniWalk\Utils\Strings;
use JuniWalk\Utils\UI\Modal;
use Nette\InvalidArgumentException;

trait ModalControlTrait
{
	use NajaAjaxRedirectTrait;

	/**
	 * @throws InvalidArgumentException
	 */
	public function openModal(Modal|string $modal, array $params = []): void
	{
		if (is_string($modal) && !Strings::startsWith($modal, '#')) {
			$modal = $this->getComponent($modal, true);
		}

		if ($modal instanceof Modal) {
			// TODO: Force modals to use Events trait, if render isWatched invoke
			// TODO: $modal->when('render', fn($m, $t) => $t->setParameters($params))
			// $modal->getTemplate()->setParameters($params);
			// $params = [];

			$modal->setModalOpen(true);
			$modal = '#'.$modal->getName();
		}

		$template = $this->getTemplate();
		$template->add('openModal', $modal);
		$template->setParameters($params);

		$this->redrawControl('modals');
		$this->redirect('this');
	}
}
