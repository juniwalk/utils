<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Console\Tools;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\OutputInterface;

final class TableControl
{
	private Table $table;
	private int $columns = 0;
	private array $rows = [];


	public function __construct(OutputInterface $output)
	{
		$this->table = new Table($output);
	}


	public function setHeaders(string ... $columns): void
	{
		$this->table->setHeaders($columns);
		$this->columns = sizeof($columns);
	}


	public function setRow(int $id, mixed ... $columns): void
	{
		$this->rows[$id] = $columns;
	}


	public function addSeparator(): void
	{
		$this->rows[] = new TableSeparator;
	}


	public function setSummary(string $message, mixed ... $columns): void
	{
		$span = $this->columns - sizeof($columns);

		$this->rows[] = new TableSeparator;
		$this->rows[] = array_merge([
			new TableCell($message, ['colspan' => $span]),
		], $columns);
	}


	public function render(): void
	{
		$this->table->setRows($this->rows);
		$this->table->render();
	}
}
