<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Stringable;

final class JsonLiteral implements Stringable
{
	public function __construct(
		private readonly string $code
	) {}


	public function __toString(): string
	{
		return Json::LITERAL.base64_encode($this->code);
	}
}
