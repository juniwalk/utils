<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Console;

use JuniWalk\Utils\Console\Input\ArrayInput;
use JuniWalk\Utils\Exceptions\CommandFailedException;
use JuniWalk\Utils\Exceptions\ConfirmationDeniedException;
use Symfony\Component\Console\Command\Command;
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
			try {
				$question($this);

			} catch (ConfirmationDeniedException) {
				$this->terminate();
				break;
			}
		}
	}


	/**
	 * @throws CommandNotFoundException
	 * @throws CommandFailedException
	 */
	protected function execCommands(array $commandList, callable $callback = null, OutputInterface $output = null): void
	{
		if (empty($commandList)) {
			return;
		}

		foreach ($commandList as $commandName => $arguments) {
			$this->execCommand($commandName, $arguments, $callback, $output);
		}
	}


	/**
	 * @throws CommandNotFoundException
	 * @throws CommandFailedException
	 */
	protected function execCommand(
		string $commandName,
		array $arguments,
		callable $callback = null,
		OutputInterface $output = null,
	): int {
		$command = $this->getApplication()->get($commandName);
		$definition = $command->getDefinition();

		if ($definition->hasArgument('command')) {
			$arguments['command'] = $commandName;
		}

		$input = new ArrayInput($arguments, $definition);
		$input->setInteractive(false);

		if ($callback && $callback($command, $input) === false) {
			return Command::SUCCESS;
		}

		$code = $command->run($input, $output ?? $this->output);

		if ($code === Command::FAILURE) {
			throw CommandFailedException::fromName($commandName);
		}

		return $code ?? Command::SUCCESS;
	}


	protected function createProcess(string $command, int $timeout = null): Process
	{
		$process = Process::fromShellCommandline($command);
		$process->setTty(Process::isTtySupported());
		$process->setTimeout($timeout);

		return $process;
	}


	protected function execShell(string ...$command): int
	{
		$process = $this->createProcess(implode(' ', $command));
		return $process->run(function($type, $buffer) {
			$this->output->write($buffer);
		});
	}


	protected function terminate(): void
	{
		$this->setCode(fn(): int => Command::SUCCESS);
		$this->input?->setInteractive(false);
	}


	protected function confirm(string $message, bool $default = true): bool
	{
		$question = new ConfirmationQuestion($message.' <comment>['.($default ? 'Y,n' : 'y,N').']</> ', $default);
		return $this->ask($question, true);
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


	/**
	 * @throws ConfirmationDeniedException
	 */
	protected function ask(Question $question, bool $throw = false): mixed
	{
		$speaker = $this->getHelper('question');
		$answer = $speaker->ask(
			$this->input,
			$this->output,
			$question,
		);

		if (!$answer && $throw && $question instanceof ConfirmationQuestion) {
			throw new ConfirmationDeniedException;
		}

		$this->output->writeln('');
		return $answer;
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
