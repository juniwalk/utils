<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use JuniWalk\Utils\Exceptions\EntityNotFoundException;
use Nette\Application\UI\Form;

abstract class AbstractRepository
{
	protected readonly EntityManager $entityManager;
	protected readonly Connection $connection;
	protected string $entityName;


	/**
	 * @throws EntityNotFoundException
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->connection = $entityManager->getConnection();
		$this->entityManager = $entityManager;

		if (!$this->entityName) {
			throw EntityNotFoundException::fromClass($this->entityName);
		}
	}


	/**
	 * @throws NoResultException
	 */
	public function getBy(callable $where, ?int $maxResults = null): array
	{
		$builder = $this->createQueryBuilder('e', 'e.id');
		$builder = $where($builder) ?: $builder;

		$query = $builder->getQuery();

		if ($maxResults && !$query->getMaxResults()) {
			$query->setMaxResults($maxResults);
		}

		return $query->getResult();
	}


	public function findBy(callable $where, ?int $maxResults = null): array
	{
		try {
			return $this->getBy($where, $maxResults);

		} catch (NoResultException) {
			return [];
		}
	}


	/**
	 * @throws NoResultException
	 */
	public function getOneBy(callable $where): object
	{
		$builder = $this->createQueryBuilder('e', 'e.id');
		$builder = $where($builder) ?: $builder;

		return $builder->getQuery()
			->setMaxResults(1)
			->getSingleResult();
	}


	public function findOneBy(callable $where): ?object
	{
		try {
			return $this->getOneBy($where);

		} catch (NoResultException) {
			return null;
		}
	}


	/**
	 * @throws NoResultException
	 */
	public function getById(int $id): object
	{
		return $this->getOneBy(function($qb) use ($id) {
			$qb->where('e.id = :id')->setParameter('id', $id);
		});
	}


	public function findById(int $id): ?object
	{
		try {
			return $this->getById($id);

		} catch (NoResultException) {
			return null;
		}
	}


	public function createQueryBuilder(string $alias, string $indexBy = null): QueryBuilder
	{
		return $this->entityManager->createQueryBuilder()->select($alias)
			->from($this->entityName, $alias, $indexBy);
	}


	public function createQuery(string $dql = null): Query
	{
		return $this->entityManager->createQuery($dql);
	}


	public function getReference(?int $id, string $entityName = null): ?object
	{
		if (!$id || empty($id)) {
			return null;
		}

		return $this->entityManager->getReference($entityName ?: $this->entityName, $id);
	}


	public function getFormReference(string $field, Form $form): ?object
	{
		return $this->getReference(
			(int) $form->getHttpData($form::DATA_LINE, $field) ?: null
		);
	}


	public function truncateTable(bool $cascade = false, string $entityName = null): void
	{
		$this->query('TRUNCATE TABLE "'.$this->getTableName($entityName).'" RESTART IDENTITY'.($cascade == true ? ' CASCADE' : null));
	}


	public function getTableName(string $entityName = null): string
	{
		$entityName = $entityName ?: $this->entityName;
		$metaData = $this->entityManager->getClassMetadata($entityName);
		$tableName = $metaData->getTableName();

		if ($schemaName = $metaData->getSchemaName()) {
			$tableName = $schemaName.'.'.$tableName;
		}

		return $tableName;
	}


	/**
	 * @throws DBALException
	 */
	private function query(string $query): mixed
	{
		return $this->connection->query($query);
	}
}
