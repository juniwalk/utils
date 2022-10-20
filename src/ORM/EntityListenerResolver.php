<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM;

use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Nette\DI\Container;

final class EntityListenerResolver extends DefaultEntityListenerResolver
{
	public function __construct(
		private readonly Container $container
	) {}


	public function resolve(/*string*/ $className)// : object
	{
		return $this->container->getByType($className);
	}
}
