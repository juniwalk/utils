<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Console\Tools;

use Throwable;
use Tracy\Debugger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

final class ProgressIndicator
{
	public const Working = '....';
	public const Success = '<info> ok </>';
	public const Warning = '<comment>warn</>';
	public const Error = '<fg=red>FAIL</>';
	public const Skipped = '<comment>skip</>';

	public const WORKING = self::Working;
	public const SUCCESS = self::Success;
	public const WARNING = self::Warning;
	public const ERROR = self::Error;
	public const SKIPPED = self::Skipped;

	private OutputInterface $errorOutput;
	private ProgressBar $progress;

	public function __construct(
		OutputInterface $output,
		int $max = 0,
		private ?bool $throwExceptions = null,
		private bool $hideOnFinish = true,
		private bool $logExceptions = true,
	) {
		$this->progress = new ProgressBar($output, $max);
		$this->errorOutput = $output;

		if (method_exists($output, 'getErrorOutput')) {
			$this->errorOutput = $output->getErrorOutput();
		}

		// ? Enable exceptions by default if we are not in CLI mode.
		$this->throwExceptions ??= php_sapi_name() <> 'cli';
	}


	public function setLogExceptions(bool $logExceptions = false): void
	{
		$this->logExceptions = $logExceptions;
	}


	public function setThrowExceptions(bool $throwExceptions = false): void
	{
		$this->throwExceptions = $throwExceptions;
	}


	public function setHideOnFinish(bool $hideOnFinish = true): void
	{
		$this->hideOnFinish = $hideOnFinish;
	}


	public function setRedrawFrequency(int $frequency = 1, float $secondsMax = 1, float $secondsMin = 0): void
	{
		$this->progress->setRedrawFrequency($frequency);
		$this->progress->maxSecondsBetweenRedraws($secondsMax);
		$this->progress->minSecondsBetweenRedraws($secondsMin);
	}


	public function setMessage(string $message, string $name = 'message'): void
	{
		$this->progress->setMessage($message, $name);
	}


	public function getMessage(string $name = 'message'): ?string
	{
		return $this->progress->getMessage($name);
	}


	public function setStatus(string $message): void
	{
		$this->progress->setMessage($message, 'status');
	}


	public function getStatus(): ?string
	{
		return $this->progress->getMessage('status');
	}


	public function execute(string $message, callable $callback): mixed
	{
		$this->progress->setFormat("[%status%] %message%\n");
		$this->setMessage($message, 'message');
		$this->setStatus(self::Working);
		$this->progress->start();

		try {
			return $callback($this);

		} catch (Throwable $e) {
			$this->setStatus(self::Error);
			$this->render($e);

		} finally {
			if ($this->getStatus() === self::Working) {
				$this->setStatus(self::Success);
			}

			$this->progress->finish();
		}

		return Command::FAILURE;
	}


	/**
	 * @param mixed[] $values
	 */
	public function iterate(iterable $values, callable $callback): void
	{
		$this->progress->setFormat("\n %percent:3s%% [%bar%] %current%/%max%\n %message%\n\n");
		$this->setMessage('<info>Preparing...</>', 'message');
		$this->progress->start(is_countable($values) ? count($values) : null);

		foreach ($values as $key => $value) {
			try {
				if ($callback($this, $value, $key) === false) {
					break;
				}

			} catch (Throwable $e) {
				$this->render($e);
			}

			$this->progress->advance();
		}

		$this->setMessage('<info>Process has finished</>');
		$this->progress->finish();

		if ($this->hideOnFinish) {
			$this->progress->clear();
		}
	}


	private function render(Throwable $e): void
	{
		$terminal = new Terminal;
		$this->progress->clear();

		$title = sprintf('  [%s]  ', get_class($e));
		$len = Helper::length($title);
		$width = $terminal->getWidth() ? $terminal->getWidth() - 1 : PHP_INT_MAX;
		$formatter = $this->errorOutput->getFormatter();
		$lines = [];

		foreach (preg_split('/\r?\n/', $e->getMessage()) ?: [] as $line) {
			foreach ($this->splitStringByWidth($line, $width - 4) as $line) {
				// pre-format lines to get the right string length
				$lineLength = Helper::length(preg_replace('/\[[^m]*m/', '', $formatter->format($line) ?: '')) + 4;
				$lines[] = array($line, $lineLength);
				$len = max($lineLength, $len);
			}
		}

		$messages = [];
		$messages[] = $emptyLine = $formatter->format(sprintf('<error>%s</>', str_repeat(' ', $len)));
		$messages[] = $formatter->format(sprintf('<error>%s%s</>', $title, str_repeat(' ', max(0, $len - Helper::length($title)))));

		foreach ($lines as $line) {
			$messages[] = $formatter->format(sprintf('<error>  %s  %s</>', $line[0], str_repeat(' ', $len - $line[1])));
		}

		$messages[] = $emptyLine;
		$messages[] = '';

		$this->errorOutput->writeln($messages);

		if ($this->logExceptions) {
			Debugger::log($e);
		}

		$this->progress->display();

		if ($this->throwExceptions) {
			throw $e;
		}
	}


	/**
	 * @return string[]
	 */
	private function splitStringByWidth(string $string, int $width): iterable
	{
		// str_split is not suitable for multi-byte characters, we should use preg_split to get char array properly.
		// additionally, array_slice() is not enough as some character has doubled width.
		// we need a function to split string not by character count but by string width
		if (false === $encoding = mb_detect_encoding($string, null, true)) {
			return str_split($string, max(1, $width));
		}

		$utf8String = mb_convert_encoding($string, 'utf8', $encoding);
		$lines = [];
		$line = '';

		foreach (preg_split('//u', $utf8String) ?: [] as $char) {
			if (Helper::width($line.$char) <= $width) {
				$line .= $char;
				continue;
			}

			$lines[] = str_pad($line, $width);
			$line = $char;
		}

		if ($line !== '') {
			$lines[] = count($lines) ? str_pad($line, $width) : $line;
		}

		mb_convert_variables($encoding, 'utf8', $lines);

		return $lines;
	}
}
