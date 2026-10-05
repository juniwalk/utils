<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

use JuniWalk\Utils\Console\Tools\ProgressIndicator;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class ProgressIndicatorTest extends TestCase
{
	public function testDebugExecuteShowsMessageOnly(): void
	{
		$output = new BufferedOutput;
		$output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
		$indicator = new ProgressIndicator($output);

		$indicator->execute('Running task', fn() => null);

		$contents = $output->fetch();
		Assert::contains('Running task', $contents);
		Assert::notContains('ok', $contents);
	}


	public function testDebugIterateShowsMessagesWithoutProgressBar(): void
	{
		$output = new BufferedOutput;
		$output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
		$output->setDecorated(true);
		$indicator = new ProgressIndicator($output);
		$indicator->setRedrawFrequency(1);

		$indicator->iterate([1, 2], function (ProgressIndicator $indicator, int $value): void {
			$indicator->setMessage(sprintf('Step %d', $value));
		});

		$contents = $output->fetch();
		Assert::contains('Step 1', $contents);
		Assert::contains('Step 2', $contents);
		Assert::false((bool) preg_match('/\[[>\- ]+\]/', $contents));
		Assert::notContains('%', $contents);
		Assert::notContains("\r", $contents);
		Assert::notContains("\033[1G", $contents);
		Assert::notContains("\033[2K", $contents);
		Assert::notContains("\033[1A", $contents);
	}


	public function testNormalOutputKeepsProgressDetails(): void
	{
		$output = new BufferedOutput;
		$indicator = new ProgressIndicator($output);
		$indicator->setRedrawFrequency(1);

		$indicator->iterate([1], static function (): void {});

		Assert::contains('[', $output->fetch());
	}
}

(new ProgressIndicatorTest)->run();
