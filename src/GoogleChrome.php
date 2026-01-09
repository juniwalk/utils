<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Nette\Application\Application;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Link;
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
	 * @throws InvalidLinkException
	 * @throws ProcessFailedException
	 */
	public function convertUrl(string|Link $url, bool $persist = false): string
	{
		$file = $this->tempDir.'/'.Random::generate().'.pdf';

		if ($persist === false) {
			$this->scheduleRemoval($file);
		}

		return $this->convert($url, $file);
	}


	/**
	 * @throws InvalidLinkException
	 * @throws ProcessFailedException
	 */
	public function downloadPdf(string|Link $url, string $fileName, bool $persist = false): FileResponse
	{
		return new FileResponse(
			$this->convertUrl($url, $persist),
			$fileName,
			'application/pdf',
		);
	}


	/**
	 * @throws InvalidLinkException
	 * @throws ProcessFailedException
	 */
	private function convert(string|Link $url, string $file): string
	{
		$info = $this->fetchUrlStats($url);

		if ($info['http_code'] >= 300) {
			throw new InvalidLinkException('Unable to access "'.$url.'" to convert to pdf.', $info['http_code']);
		}

		$this->setOption('print-to-pdf', $file);
		$command = ['/usr/bin/google-chrome'];

		foreach ($this->toArguments() as $argument) {
			$command[] = $argument;
		}

		$command[] = '"'.$url.'"';

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


	private function scheduleRemoval(string $file): void
	{
		$this->application->onShutdown[] = fn() => @unlink($file); // @phpstan-ignore assign.propertyType (Callback has different structure, we dont care)
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


	/**
	 * @return array{
	 * 		http_code: int,
	 * 		content_type: ?string,
	 * 		download_content_length: float
	 * }
	 * @throws InvalidLinkException
	 */
	private function fetchUrlStats(string|Link $url, int $timeout = 10, int $maxRedirs = 3): array
	{
		if (!$curl = curl_init((string) $url)) {
			throw new InvalidLinkException('Unable to stat remote url.', 500);
		}

		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_MAXREDIRS, $maxRedirs);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);

		if (!curl_exec($curl) || !$info = curl_getinfo($curl)) {
			throw new InvalidLinkException('Unable to stat remote url.', 500);
		}

		return $info;
	}
}
