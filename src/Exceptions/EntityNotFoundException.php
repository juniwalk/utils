<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2021
 * @license   MIT License
 */

namespace JuniWalk\Utils\Exceptions;

final class EntityNotFoundException extends \RuntimeException
{
	public static function fromEntity(object $entity): static
	{
		return static::fromClass(get_class($entity));
	}


	public static function fromClass(string $entityName): static
	{
		return new static('Entity '.$entityName.' was not found.');
	}
}
