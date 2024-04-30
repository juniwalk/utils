<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

use JuniWalk\Utils\Parse;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class ParseTest extends TestCase
{
	public function testArguments(): void
	{
		$items = 'test=true, west: false, next => null';

		Assert::same(
			['test' => true, 'west' => false, 'next' => null],
			Parse::arguments($items)
		);
	}
}

(new ParseTest)->run();
