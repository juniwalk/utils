<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Exceptions;

use Nette\Application\ForbiddenRequestException;

final class PermissionDeniedException extends ForbiddenRequestException
{
	public static function fromTask(string $resource, string $task, ?self $previous = null): self
	{
		return new self('Access denied for task '.$resource.':'.$task, 403, $previous);
	}
}
