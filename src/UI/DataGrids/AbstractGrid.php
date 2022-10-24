<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\DataGrids;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\LabeledEnum;
use JuniWalk\Utils\Html;
use Nette\Application\UI\Control;
use Nette\Localization\Translator;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\DataGrid;

abstract class AbstractGrid extends Control
{
	protected DataGrid $grid;
	protected Translator $translator;
	protected bool $hasFiltersAlwaysShown = true;
	protected bool $isDisabled = false;
	protected ?string $title = null;


	public function setDisabled(bool $disabled = true): void
	{
		$this->isDisabled = $disabled;
	}


	public function isDisabled(): bool
	{
		return $this->isDisabled;
	}


	public function setFilter(iterable $filter): void
	{
		$this->grid->setFilter($filter);
	}


	public function setFiltersAlwaysShown(bool $filtersAlwaysShown = true): void
	{
		$this->hasFiltersAlwaysShown = $filtersAlwaysShown;
	}


	public function hasFiltersAlwaysShown(): bool
	{
		return $this->hasFiltersAlwaysShown;
	}


	public function setTitle(?string $title): void
	{
		$this->title = $title;
	}


	public function getTitle(): ?string
	{
		return $this->title;
	}


	public function setTranslator(Translator $translator = null): void
	{
		$this->translator = $translator;
	}


	public function getTranslator(): ?Translator
	{
		return $this->translator;
	}


	/**
	 * @throws UnexpectedValueException
	 */
	public function addColumnEnum(string $name, string $title, string $enum, bool $hasBlockButtons = false): Column
	{
		$signalMethod = $this->formatSignalMethod($name);

		if (!method_exists($this, $signalMethod)) {
			return $this->grid->addColumnText($name, $title)->setAlign('right')
				->setRenderer(function($item) use ($name): Html {
					$enum = $item->{'get'.$name}();
					return Html::enumBadge($enum);
				});
		}

		if (!enum_exists($enum) || !is_a($enum, LabeledEnum::class, true)) {
			throw new \UnexpectedValueException('$enum has to be instance of '.LabeledEnum::class);
		}

		$column = $this->grid->addColumnStatus($name, $title)->setAlign('right');
		$column->onChange[] = function($id, $value) use ($signalMethod, $enum): void {
			$this->$signalMethod((int) $id, $enum::tryFrom($value));
		};

		foreach ($enum::cases() as $item) {
			$class = Color::Secondary->for('btn');

			if (method_exists($item, 'color')) {
				$class = $item->color()->for('btn');
			}

			if ($hasBlockButtons == true) {
				$class .= ' btn-block text-left';
			}

			$option = $column->addOption($item->value, $item->label())
				->setClass($class);

			if (method_exists($item, 'icon') && $icon = $item->icon()) {
				$option->setIcon($icon)->setIconSecondary($icon);
			}

			$option->endOption();
		}

		return $column;
	}


	final public function redrawGrid(): void
	{
		$this->grid->redrawControl();
		$this->getPresenter()->redirectAjax('this');
	}


	final public function redrawItem(int $id): void
	{
		$this->grid->redrawItem($id);
		$this->getPresenter()->redirectAjax('this');
	}


	final public function render()
	{
		$gridTemplate = $this->getComponent('grid')->getTemplate();
		$gridTemplate->controlName = $this->getName();
		$gridTemplate->hasFiltersAlwaysShown = $this->hasFiltersAlwaysShown;
		$gridTemplate->isDisabled = $this->isDisabled;
		$gridTemplate->title = $this->title;

		$template = $this->getTemplate();
		$template->setFile(__DIR__.'/templates/datagrid-wrapper.latte');
		$template->render();
	}


	protected function createModel(): mixed
	{
		return [];
	}


	abstract protected function createComponentGrid(): DataGrid;


	final protected function createDataGrid(bool $rememberState = true, string $primaryKey = null): DataGrid
	{
		$grid = $this->grid = new DataGrid;
		$grid->setRememberState($rememberState);
		$grid->setRefreshUrl(!$rememberState);
		$grid->setCustomPaginatorTemplate(__DIR__.'/templates/datagrid_paginator.latte');
		$grid->setTemplateFile(__DIR__.'/templates/datagrid.latte');
		$grid->setItemsPerPageList([10, 20, 50], false);
		$grid->setDefaultPerPage(20);

		if (isset($primaryKey)) {
			$grid->setPrimaryKey($primaryKey);
		}

		$grid->setDataSource($this->createModel());
		$grid->setStrictSessionFilterValues(false);
		$grid->setOuterFilterRendering(true);
		$grid->setOuterFilterColumnsCount(3);

		if ($this->translator instanceof Translator) {
			$grid->setTranslator($this->translator);
		}

		DataGrid::$iconPrefix = 'fas fa-fw fa-';

		return $grid;
	}
}
