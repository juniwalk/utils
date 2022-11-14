<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Utils;

use Doctrine\ORM\Mapping\EntityListenerResolver;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;

class NetteEntityListenerResolver implements EntityListenerResolver
{
	public function __construct(
		private readonly Container $container
	) {}


    /**
     * @inheritdoc
     */
	public function clear(/*string*/ $className = null)//: void
	{
		if (is_null($className)) {
			return;
		}

		$this->container->removeService($className);
	}


	/**
     * @inheritdoc
	 * @throws MissingServiceException
	 */
	public function resolve(/*string*/ $className)//: object
	{
		return $this->container->getByType($className);
	}


    /**
     * @inheritdoc
     */
	public function register(/*object*/ $resolver)//: void
	{
		$this->container->addService(get_class($resolver), $resolver);
	}
}
