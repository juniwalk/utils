<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Interfaces;

use JuniWalk\Utils\Enums\SignoutReason;

/**
 * @phpstan-type LinkArgs array<string, mixed>
 */
interface SignInHandler
{
	/**
	 * @param LinkArgs $params
	 */
	public function forceSignIn(SignoutReason $reason, bool $storeRequest = false, array $params = []): void;
}
