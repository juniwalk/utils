<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Actions;

use JuniWalk\Utils\Html;

interface Action
{
	public function create(): Html;
	public function render(): void;
}
