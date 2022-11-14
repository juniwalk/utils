<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Exceptions;

final class EntityNotValidException extends \RuntimeException
{
	public static function fromEntity(object $entity): static
	{
		return static::fromClass(get_class($entity));
	}


	public static function fromClass(string $entityName): static
	{
		return new static('Entity '.$entityName.' is not valid.');
	}
}
