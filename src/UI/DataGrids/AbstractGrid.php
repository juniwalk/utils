<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\DataGrids;

use JuniWalk\Utils\Enums\LabeledEnum;
use JuniWalk\Utils\Html;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\DataGrid;

abstract class AbstractGridNew extends DataGrid
{
	protected string $title;
	protected bool $isDisabled = false;
	protected bool $hasFiltersAlwaysShown = true;


	public function __construct()
	{
		static::$iconPrefix = 'fas fa-fw fa-';
		parent::__construct(null, null);

		$this->setCustomPaginatorTemplate(__DIR__.'/templates/datagrid_paginator.latte');
		$this->setStrictSessionFilterValues(false);
		$this->setOuterFilterRendering(true);
		$this->setOuterFilterColumnsCount(3);
		$this->setRememberState(true);
		$this->setRefreshUrl(false);

		$this->setItemsPerPageList([10, 20, 50], false);
		$this->setDefaultPerPage(20);

		$this->setDataSource($this->createModel());
		$this->createColumns();
	}


	public function setTitle(string $title): void
	{
		$this->title = $title;
	}


	public function getTitle(): ?string
	{
		return $this->title;
	}


	public function setDisabled(bool $disabled = true): void
	{
		$this->isDisabled = $disabled;
	}


	public function isDisabled(): bool
	{
		return $this->isDisabled;
	}


	public function setFiltersAlwaysShown(bool $filtersAlwaysShown = true): void
	{
		$this->hasFiltersAlwaysShown = $filtersAlwaysShown;
	}


	public function hasFiltersAlwaysShown(): bool
	{
		return $this->hasFiltersAlwaysShown;
	}


	public function getOriginalTemplateFile(): string
	{
		return __DIR__.'/templates/datagrid.latte';
	}


	/**
	 * @throws UnexpectedValueException
	 */
	public function addColumnEnum(string $name, string $title, string $enum, bool $hasBlockButtons = false): Column
	{
		$signalMethod = $this->formatSignalMethod($name);

		if (!method_exists($this, $signalMethod)) {
			return $this->addColumnText($name, $title)->setAlign('right')
				->setRenderer(function($item) use ($name): Html {
					$enum = $item->{'get'.$name}();
					return Html::enumBadge($enum);
				});
		}

		if (!enum_exists($enum) || !is_a($enum, LabeledEnum::class, true)) {
			throw new \UnexpectedValueException('$enum has to be instance of '.LabeledEnum::class);
		}

		$column = $this->addColumnStatus($name.'Scalar', $title)->setAlign('right');
		$column->onChange[] = function($id, $value) use ($signalMethod, $enum): void {
			$this->$signalMethod((int) $id, $enum::tryFrom($value));
		};

		foreach ($enum::cases() as $item) {
			$class = 'btn-secondary';

			if (method_exists($item, 'color')) {
				$class = $item->color()->for('btn');
			}

			if ($hasBlockButtons == true) {
				$class .= ' btn-block text-left';
			}

			$option = $column->addOption($item->value, $item->label())->setClass($class);

			if (method_exists($item, 'icon') && $icon = $item->icon()) {
				$option->setIcon($icon)->setIconSecondary($icon);
			}

			$option->endOption();
		}

		return $column;
	}


	final public function redrawItem($id, $primaryWhereColumn = null): void
	{
		parent::redrawItem($id, $primaryWhereColumn);
		$this->getPresenter()->redirectAjax('this');
	}


	final public function redrawGrid(): void
	{
		$this->redrawControl();
		$this->getPresenter()->redirectAjax('this');
	}


	final public function render(): void
	{
		$template = $this->getTemplate();
		$template->controlName = $this->getName();
		$template->hasFiltersAlwaysShown = $this->hasFiltersAlwaysShown;
		$template->isResponsive = $this->isResponsive;
		$template->isDisabled = $this->isDisabled;
		$template->title = $this->title;

		parent::render();
	}


	protected function createModel(): mixed
	{
		return [];
	}


	abstract protected function createColumns(): void;
}
