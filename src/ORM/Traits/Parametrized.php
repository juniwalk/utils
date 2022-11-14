<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JsonSerializable;
use JuniWalk\Utils\Strings;

trait Parametrized
{
	#[ORM\Column(type: 'json')]
	private array $params = [];


	/**
	 * @throws InvalidArgumentException
	 */
	public function setParam(string $key, mixed $value, bool $overwrite = true): void
	{
		if (is_object($value) && !$value instanceof JsonSerializable) {
			throw new InvalidArgumentException('Object instances have to implement JsonSerializable');
		}

		if (!$overwrite && $this->hasParam($key)) {
			return;
		}

		$key = Strings::lower($key);
		$this->params[$key] = $value;

		if (is_null($value)) {
			unset($this->params[$key]);
		}
	}


	public function getParam(string $key): mixed
	{
		if (!$this->hasParam($key)) {
			return null;
		}

		$key = Strings::lower($key);
		return $this->params[$key] ?? null;
	}


	public function getParams(): array
	{
		return $this->params;
	}


	public function hasParam(string $key): bool
	{
		$key = Strings::lower($key);
		return isset($this->params[$key]);
	}
}
