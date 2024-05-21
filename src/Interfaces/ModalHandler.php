<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Interfaces;

use JuniWalk\Utils\Interfaces\Modal;

interface ModalHandler
{
	/**
	 * @param array<string, mixed> $params
	 */
	public function openModal(Modal|string $modal, array $params = []): void;
}
