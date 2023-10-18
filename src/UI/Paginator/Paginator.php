<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Paginator;

use Countable;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as QueryWrapper;
use IteratorAggregate;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator as NettePages;
use Nette\Localization\Translator;
use Traversable;

/**
 * @method void onChange(int $page, int $perPage)
 */
final class Paginator extends Control implements Countable, IteratorAggregate
{
	/** @var callable[] */
	public array $onChange = [];

	private QueryWrapper $result;
	private readonly NettePages $pages;
	private readonly Translator $translator;
	private bool $isAjax = true;
	private array $perPages = [10, 20, 50];
	private int $maxPages = 9;

	public function __construct(int $page, int $perPage, Translator $translator)
	{
		$this->translator = $translator;
		$this->pages = new NettePages;
		$this->pages->setItemsPerPage($perPage);
		$this->pages->setPage($page);
	}


	public function setMaxPages(int $maxPages): void
	{
		$this->maxPages = $maxPages;
	}


	public function handlePage(int $page): void
	{
		$this->pages->setPage($page);

		$this->onChange(
			$this->pages->getPage(),
			$this->pages->getItemsPerPage(),
		);
	}


	public function handlePerPage(int $perPage): void
	{
		$this->pages->setItemsPerPage($perPage);
		$this->pages->setPage(1);

		$this->onChange(
			$this->pages->getPage(),
			$this->pages->getItemsPerPage(),
		);
	}


	public function setQuery(Query|QueryBuilder $query, bool $fetchJoinCollection = true): void
	{
		$query->setMaxResults($this->pages->getItemsPerPage());
		$query->setFirstResult($this->pages->getOffset());

		$this->result = new QueryWrapper($query, $fetchJoinCollection);
		$this->pages->setItemCount($this->result->count());
	}


	public function setAjax(bool $ajax): void
	{
		$this->isAjax = $ajax;
	}


	public function getIterator(): Traversable
	{
		return $this->result?->getIterator();
	}


	public function getResult(): QueryWrapper
	{
		return $this->result;
	}


	public function count(): int
	{
		return $this->result?->count() ?? 0;
	}


	public function render(): void
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/default.latte');
		$template->render();
	}


	public function renderPages(): void
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/pages.latte');
		$template->setParameters([
			'steps' => $this->createSteps(),
			'link' => $this->link(...),
			'isAjax' => $this->isAjax,
			'pages' => $this->pages,
		]);

		$template->render();
	}


	public function renderPerPage(): void
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/perpage.latte');
		$template->setParameters([
			'isAjax' => $this->isAjax,
			'pages' => $this->pages,
			'perPages' => $this->perPages,
		]);

		$template->render();
	}


	protected function createComponentPerPage(): Form
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->addSelect('perPage')->setItems($this->perPages, false)
			->setHtmlAttribute('data-auto-submit');
		$form->addSubmit('submit');
		$form->setDefaults([
			'perPage' => $this->pages->getItemsPerPage(),
		]);

		$form->onSuccess[] = function($form, $data) {
			$this->handlePerPage($data->perPage);
		};

		return $form;
	}


	protected function createSteps(): array
	{
		$pageCount = $this->pages->getPageCount();
		$page = $this->pages->getPage();

		if ($pageCount <= 1) {
			return [];
		}

		if ($pageCount <= $this->maxPages) {
			return range(
				$this->pages->getFirstPage(),
				$pageCount,
			);
		}

		$slidingStart = min(
			$pageCount - $this->maxPages + 2,
			$page - floor(($this->maxPages - 3) / 2),
		);

		if ($slidingStart < 2) $slidingStart = 2;

		$slidingEnd = min(
			$slidingStart + $this->maxPages - 3,
			$pageCount - 1,
		);

		$pages = [1];

		if ($slidingStart > 2) {
			$pages[] = null;
		}

		$pages = array_merge($pages, range($slidingStart, $slidingEnd));

		if ($slidingEnd < $pageCount - 1) {
			$pages[] = null;
		}

		$pages[] = $pageCount;

		return $pages;
	}
}
