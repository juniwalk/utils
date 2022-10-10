<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Console;

use JuniWalk\Utils\Exceptions\CommandFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

abstract class AbstractCommand extends Command
{
	private OutputInterface $output;
	private InputInterface $input;

	/** @var callable[] */
	private array $questions = [];


	public function addQuestion(callable $question): void
	{
		$this->questions[] = $question;
	}


	protected function initialize(InputInterface $input, OutputInterface $output): void
	{
		$this->output = $output;
		$this->input = $input;
	}


	protected function interact(InputInterface $input, OutputInterface $output): void
	{
		if (empty($this->questions)) {
			return;
		}

		foreach ($this->questions as $question) {
			$answer = $question($this);
			$output->writeln('');

			if ($answer === false) {
				$this->terminate();
			}
		}
	}


	/**
	 * @throws CommandNotFoundException
	 * @throws CommandFailedException
	 */
	protected function execCommands(array $commandList, callable $callback = null): void
	{
		$application = $this->getApplication();

		if (empty($commandList)) {
			return;
		}

		foreach ($commandList as $commandName => $arguments) {
			$command = $application->get($commandName);

			$input = new ArrayInput($arguments, $command->getDefinition());
			$input->setInteractive(false);

			if ($callback && $callback($command, $input) === false) {
				continue;
			}

			$code = $command->run($input, $this->output);

			if ($code === Command::FAILURE) {
				throw CommandFailedException::fromName($commandName);
			}
		}
	}


	protected function execShell(string ... $command): int
	{
		$command = implode(' ', $command);

		$process = Process::fromShellCommandline($command);
		$process->setTty(Process::isTtySupported());

		return $process->run(function($type, $buffer) {
			$this->output->write($buffer);
		});
	}


	protected function terminate(): void
	{
		$this->setCode(function(): int {
			return Command::SUCCESS;
		});
	}


	protected function confirm(string $message, bool $default = true): bool
	{
		$question = new ConfirmationQuestion($message.' <comment>[Y,n]</> ', $default);
		return $this->ask($question);
	}


	protected function choose(string $message, array $choices, mixed $default = null): mixed
	{
		$default = $default ?? array_keys($choices)[0];

		if (sizeof($choices) == 1) {
			return $choices[$default];
		}

		$question = new ChoiceQuestion($message.' <comment>['.$choices[$default].']</> ', $choices, $default);
		return $this->ask($question);
	}


	protected function ask(Question $question): mixed
	{
		return $this->getHelper('question')->ask($this->input, $this->output, $question);
	}


	protected function writeHeader(string $message, int $width = 68): void
	{
		$message = str_pad($message, $width, ' ', STR_PAD_BOTH);

		$this->output->writeln('');
		$this->output->writeln('<fg=black;bg=#00cdcd>'.str_repeat(' ', $width).'</>');
		$this->output->writeln('<fg=black;bg=#00cdcd>'.$message.'</>');
		$this->output->writeln('<fg=black;bg=#00cdcd>'.str_repeat(' ', $width).'</>');
		$this->output->writeln('');
	}
}