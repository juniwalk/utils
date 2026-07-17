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
	public function testFloat(): void
	{
		Assert::same(null, Sanitize::float('text'));
		Assert::same(null, Sanitize::float(''));
		Assert::same(123.0, Sanitize::float('123'));
		Assert::same(123.0, Sanitize::float(123));
		Assert::same(12.34, Sanitize::float(12.34));
		Assert::same(12.34, Sanitize::float('12,34'));
		Assert::same(1000.0, Sanitize::float('1e3'));
		Assert::same(-5.2, Sanitize::float('-5.2'));
		Assert::same(1.0, Sanitize::float(true));
		Assert::same(null, Sanitize::float(false));
		Assert::same(null, Sanitize::float(0));
		Assert::same(null, Sanitize::float('0'));
		Assert::same(12.35, Sanitize::float('12,345', 2));
		Assert::same(12.345, Sanitize::float('12,345', 0));
	}
}

(new SanitizeTest)->run();
