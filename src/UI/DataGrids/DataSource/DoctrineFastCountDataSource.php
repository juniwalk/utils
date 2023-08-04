<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2021
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\DataGrids\DataSource;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;

/**
 * @method void onDataLoaded(array $result)
 */
final class DoctrineFastCountDataSource extends DoctrineDataSource
{
	private DataGrid $datagrid;

	public function setDataGrid(DataGrid $datagrid): void
	{
		$this->datagrid = $datagrid;
	}


	public function getCount(): int
	{
		$entityManager = $this->dataSource->getEntityManager();
		$entityNames = $this->dataSource->getRootEntities();
		$connection = $entityManager->getConnection();
		$platform = $connection->getDatabasePlatform();

		if ($this->isFiltered() || empty($entityNames) || !$platform instanceof PostgreSQL94Platform) {
			// return $this->calculateSlidingCount();
			return parent::getCount();
		}

		$metaData = $entityManager->getClassMetadata($entityNames[0]);
		$tableName = $metaData->getTableName();

		if ($schemaName = $metaData->getSchemaName()) {
			$tableName = $schemaName.'.'.$tableName;
		}

		try {
			return $connection->fetchOne(
				"SELECT reltuples::bigint AS count FROM pg_class WHERE oid = '{$tableName}'::regclass;"
			);

		} catch (DBALException $e) {
			// return $this->calculateSlidingCount();
			return parent::getCount();
		}
	}


	private function isFiltered(): bool
	{
		return (bool) $this->dataSource->getDQLPart('where');
	}


	private function calculateSlidingCount(): int
	{
		if (!$this->datagrid || !$component = $this->datagrid->getPaginator()) {
			// return parent::getCount(); // ? could be?
			return 0;
		}

		$paginator = $component->getPaginator();
		return ($paginator->getPage() + 1) * $paginator->getItemsPerPage();
	}
}
