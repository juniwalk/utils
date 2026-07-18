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


	public function testNumber_invalidAndEmptyValues(): void
	{
		Assert::same(null, Parse::number('text'));
		Assert::same(null, Parse::number(''));
		Assert::same(null, Parse::number(true));
		Assert::same(null, Parse::number(false));

		// Reject silent concatenation of numbers
		Assert::same(null, Parse::number('12abc34'));
		Assert::same(null, Parse::number('12abc34kg'));
	}


	public function testNumber_plainNumericValues(): void
	{
		Assert::same('123', Parse::number('123'));
		Assert::same('123', Parse::number(123));
		Assert::same('12.34', Parse::number(12.34));
	}


	public function testNumber_exponentsAndNegativeNumbers(): void
	{
		Assert::same('1e3', Parse::number('1e3'));
		Assert::same('-5.2', Parse::number('-5.2'));
		Assert::same('1e3', Parse::number('1e3kg'));
		Assert::same('1e-3', Parse::number('1e-3'));
	}


	public function testNumber_localeFormattedNumbersAndUnits(): void
	{
		Assert::same('12.34', Parse::number('12,34'));
		Assert::same('1234.56', Parse::number('1,234.56'));
		Assert::same('1234.56', Parse::number('1.234,56'));
		Assert::same('5000.50', Parse::number('5 000,50 kg'));
		Assert::same('5000.50', Parse::number('EUR 5.000,50'));
		Assert::same('5', Parse::number('5kg'));
	}


	public function testNumber_zeroHandling(): void
	{
		Assert::same('0', Parse::number(0));
		Assert::same('0', Parse::number('0'));
		Assert::same('-0', Parse::number('-0'));
		Assert::same('0.00', Parse::number('0.00'));
	}


	public function testNumber_multipleNumericTokensAreRejected(): void
	{
		Assert::same(null, Parse::number('1kg 2g'));
		Assert::same(null, Parse::number('5 + 6'));
	}
}

(new ParseTest)->run();
