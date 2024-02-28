<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\DataGrids\Tools;

use Closure;
use JuniWalk\ORM\Entity\Interfaces\Sortable;
use JuniWalk\ORM\Exceptions\EntityNotValidException;
use Ublaboo\DataGrid\DataGrid;

class SortableHandler
{
	private ?string $order;

	public function __construct(
		private readonly string $column,
		private readonly DataGrid $grid,
		private readonly Closure $callback,
	) {
		$grid->findSessionValues();
		$grid->findDefaultSort();

		$this->order = $grid->sort[$column] ?? null;
	}


	/**
	 * @throws EntityNotValidException
	 */
	public function sort(int $itemId, ?int $prevId, ?int $nextId): void
	{
		[$prevId, $nextId] = match($this->order) {
			'ASC'	=> [$nextId, $prevId],
			default	=> [$prevId, $nextId],
		};

		$items = ($this->callback)($this->column, $this->order);
		$order = sizeof($items) - 1;

		$hasToMoveDown = $items[$itemId]?->getOrder() >= (int) ($items[$nextId] ?? null)?->getOrder();
		$hasToMoveUp = $items[$itemId]?->getOrder() <= (int) ($items[$nextId] ?? null)?->getOrder();

		foreach ($items as $id => $item) {
			if (!$item instanceof Sortable) {
				throw new EntityNotValidException($item::class.' has to implement '.Sortable::class);
			}

			if ($id === $itemId && ($prevId || $nextId)) {
				continue;
			}

			if ($id === $nextId && $hasToMoveUp) {
				$items[$itemId]->setOrder($order--);
			}

			$item->setOrder($order--);

			if ($id === $prevId && $hasToMoveDown) {
				$items[$itemId]->setOrder($order--);
			}
		}
	}
}
