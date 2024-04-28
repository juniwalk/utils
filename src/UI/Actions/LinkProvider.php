<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Actions;

use Nette\Application\UI\Link;

interface LinkProvider
{
	/**
	 * @param array<string, scalar> $args
	 */
	public function createLink(Link|string $dest, array $args = []): Link|string;
}
