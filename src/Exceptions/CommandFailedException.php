<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2021
 * @license   MIT License
 */

namespace JuniWalk\Utils\Exceptions;

final class CommandFailedException extends \RuntimeException
{
	/**
	 * @param  string  $commandName
	 * @return static
	 */
	public static function fromName(string $commandName): self
	{
		return new static('Command "'.$commandName.'" has failed to execute', 1);
	}
}
