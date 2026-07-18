<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

use JuniWalk\Utils\Sanitize;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class SanitizeTest extends TestCase
{
	public function testNumber_convertsParsedValuesToFloat(): void
	{
		Assert::same(123.0, Sanitize::number('123'));
		Assert::same(1234.56, Sanitize::number('1.234,56'));
		Assert::same(1000.0, Sanitize::number('1e3'));
		Assert::same(0.0, Sanitize::number('0'));
		Assert::same(5.0, Sanitize::number('5kg'));
	}


	public function testNumber_precisionHandling(): void
	{
		Assert::same(12.35, Sanitize::number('12,345', 2));
		Assert::same(12.0, Sanitize::number('12,345', 0));
	}
}

(new SanitizeTest)->run();
