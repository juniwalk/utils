<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use Doctrine\ORM\NoResultException;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Response;

trait DoctrineNoResultHandler
{
	public function run(Request $request): Response
	{
		try {
			return parent::run($request);

		} catch (NoResultException) {
			throw new BadRequestException;
		}
	}
}
