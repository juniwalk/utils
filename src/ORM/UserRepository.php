<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM;

use JuniWalk\Utils\Exceptions\EntityNotFoundException;
use JuniWalk\Utils\Exceptions\EntityNotValidException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\NoResultException;

abstract class UserRepository extends AbstractRepository
{
	/**
	 * @throws EntityNotFoundException
	 * @throws EntityNotValidException
	 */
	public function __construct(EntityManager $entityManager)
	{
		parent::__construct($entityManager);

		if (!is_subclass_of($this->entityName, User::class)) {
			throw EntityNotValidException::fromClass($this->entityName);
		}
	}


	/**
	 * @throws NoResultException
	 */
	public function getByEmail(string $email): User
	{
		return $this->getOneBy(function($qb) use ($email) {
			$qb->where('LOWER(e.email) = LOWER(:email)')
				->setParameter(':email', $email);
		});
	}


	public function findByEmail(string $email): ?User
	{
		try {
			return $this->getByEmail($email);

		} catch (NoResultException) {
			return null;
		}
	}


	public function findByName(string $name, callable $where = null, ?int $maxResults = 5): iterable
	{
		$where = function($qb) use ($where, $name) {
			if (is_callable($where)) {
				$qb = $where($qb) ?: $qb;
			}

			$qb->andWhere('LOWER(e.name) LIKE LOWER(:name)')
				->setParameter(':name', '%'.$name.'%')
				->orderBy('e.id', 'ASC');
		};

		return $this->findBy($where, $maxResults);
	}
}
