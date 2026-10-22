<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils\Exceptions;

use Contributte\Translation\Wrappers\Message;
use JuniWalk\Utils\Enums\Color;
use Nette\Application\ForbiddenRequestException;
use Stringable;

final class PermissionDeniedException extends ForbiddenRequestException
{
	private Color $color;

	private ?string $resource = null;
	private ?string $task = null;


	/**
	 * @deprecated Use PermissionDeniedException::permissionDenied() instead.
	 */
	public static function fromTask(string $resource, string $task, ?self $previous = null): self
	{
		return self::prohibited($resource, $task, $previous);
	}


	public static function prohibited(string $resource, string $task, ?self $previous = null): self
	{
		$self = new self('web.message.permission-denied', 403, $previous);
		$self->color = Color::Warning;
		$self->resource = $resource;
		$self->task = $task;

		return $self;
	}


	public static function operationNotAllowed(): self
	{
		$self = new self('web.message.operation-not-allowed', 403);
		$self->color = Color::Warning;

		return $self;
	}


	public function getColor(): Color
	{
		return $this->color;
	}


	public function getResource(): ?string
	{
		return $this->resource;
	}


	public function getTask(): ?string
	{
		return $this->task;
	}


	public function createMessage(): Stringable|string
	{
		if (!class_exists(Message::class)) {
			return $this->message;
		}

		return new Message($this->message, ...[
			'resource' => $this->resource,
			'task' => $this->task,
		]);
	}
}
