<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Nette\Application\Application;
use Nette\InvalidArgumentException;
use Nette\Utils\Random;
use Tracy\ILogger;

/**
 * @phpstan-type Option int|float|string|null
 */
final class GoogleChrome
{
	/** @var array<string, Option> */
	private array $options = [
		// 'no-sandbox' => null,
		'headless' => 'new',
		'disable-gpu' => null,
		'virtual-time-budget' => 150,
		'run-all-compositor-stages-before-draw' => null,
		'no-pdf-header-footer' => null,
	];

	public function __construct(
		private string $tempDir,
		private Application $application,
		private ILogger $logger,
	) {
	}


	/**
	 * @param  Option $value
	 * @throws InvalidArgumentException
	 */
	public function setOption(string $key, mixed $value = null): self
	{
		if (is_numeric($key)) {
			throw new InvalidArgumentException('Option key must be non-numeric string, given: '.$key);
		}

		$this->options[$key] = $value;
		return $this;
	}


	/**
	 * @return Option
	 */
	public function getOption(string $key): mixed
	{
		if (!$this->hasOption($key)) {
			return null;
		}

		return $this->options[$key];
	}


	/**
	 * @return array<string, Option>
	 */
	public function getOptions(): array
	{
		return $this->options;
	}


	public function hasOption(string $key): bool
	{
		return isset($this->options[$key]);
	}


	public function removeOption(string $key): self
	{
		unset($this->options[$key]);
		return $this;
	}


	/**
	 * @throws InvalidArgumentException
	 * @throws ProcessFailedException
	 */
	public function covert(string $source, bool $keepFile = false): string
	{
		$file = $this->tempDir.'/'.Random::generate().'.pdf';
		$this->application->onShutdown[] = function() use ($file, $keepFile) {
			if ($keepFile) {
				return;
			}

			@unlink($file);
		};

		$this->setOption('print-to-pdf', $file);
		$command = ['/usr/bin/google-chrome'];

		foreach ($this->toArguments() as $argument) {
			$command[] = $argument;
		}

		$command[] = '"'.$source.'"';

		$process = Process::fromShellCommandline(
			command: implode(' ', $command),
			timeout: 300,
		);

		$this->logger->log($process->getCommandLine(), 'google-chrome');

		if ($process->run() > 0) {
		    throw new ProcessFailedException($process);
		}

		return $file;
	}


	/**
	 * @return string[]
	 */
	private function toArguments(): array
	{
		$arguments = [];

		if (empty($this->options)) {
			return $arguments;
		}

		foreach ($this->options as $key => $value) {
			$arguments[] = '--'.$key.(!is_null($value) ? '='.$value : null);
		}

		return $arguments;
	}
}
