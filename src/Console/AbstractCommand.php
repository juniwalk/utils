<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

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
		$this->input = $input;
		$this->output = $output;

		$formatter = $output->getFormatter();
		$formatter->setStyle('blue', new OutputFormatterStyle('blue'));
		$formatter->setStyle('fail', new OutputFormatterStyle('red'));
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


	protected function terminate(): void
	{
		$this->setCode(function(): int {
			return Command::SUCCESS;
		});
	}


	protected function ask(Question $question): mixed
	{
		return $this->getHelper('question')->ask($this->input, $this->output, $question);
	}


	protected function confirm(string $message, bool $default = true): bool
	{
		return $this->ask(new ConfirmationQuestion(
			$message.' <comment>[Y,n]</comment> ',
			$default
		));
	}


	protected function choose(string $message, array $choices, mixed $default = null): mixed
	{
		$default = $default ?? array_keys($choices)[0];

		if (sizeof($choices) == 1) {
			return $choices[$default];
		}

		return $this->ask(new ChoiceQuestion(
			$message.' <comment>['.$choices[$default].']</comment> ',
			$choices,
			$default
		));
	}
}
