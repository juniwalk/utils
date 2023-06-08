<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Paginator;

interface PaginatorFactory
{
	public function create(int $page, int $perPage): Paginator;
}
