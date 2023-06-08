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
	private array $perPages = [10, 20, 50];

	public function __construct(int $page, int $perPage, Translator $translator)
	{
		$this->translator = $translator;
		$this->pages = new NettePages;
		$this->pages->setItemsPerPage($perPage);
		$this->pages->setPage($page);
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

		$template->add('link', $this->link(...));
		$template->add('pages', $this->pages);
		$template->add('steps', range(
			$this->pages->getFirstPage(),
			$this->pages->getPageCount(),
		));

		$template->render();
	}


	public function renderPerPage(): void
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/perpage.latte');

		$template->add('perPages', $this->perPages);
		$template->add('pages', $this->pages);

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
}
