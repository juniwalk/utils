<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Utils;

use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\SqlWalker;

/**
 * The SortableNullsWalker is a TreeWalker that walks over a DQL AST and constructs
 * the corresponding SQL to allow ORDER BY x ASC NULLS FIRST|LAST.
 * @see https://gist.github.com/doctrinebot/ccd63ae93fb80415323d
 *
 * [use]
 * $query = $qb->getQuery();
 * $query->setHint(Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);
 * $query->setHint(SortableNullsWalker::FIELDS_KEY, [
 *		'p.firstname' => SortableNullsWalker::NULLS_FIRST,
 *	]);
 */
class SortableNullsWalker extends SqlWalker
{
	/** @var string */
	public const FIELDS_KEY = 'sortableNulls.fields';

	/** @var string */
	public const NULLS_FIRST = 'NULLS FIRST';
	public const NULLS_LAST = 'NULLS LAST';


	/**
	 * {@inheritDoc}
	 */
	public function walkOrderByItem($orderByItem)
	{
		$hint = $this->getQuery()->getHint(self::FIELDS_KEY);
		$sql = parent::walkOrderByItem($orderByItem);

		if (empty($hint) || !is_array($hint)) {
			return $sql;
		}

		$expr = $orderByItem->expression;
		$type = strtoupper($orderByItem->type);

		if (!$expr instanceof PathExpression || $expr->type != PathExpression::TYPE_STATE_FIELD) {
			return $sql;
		}

		$index = $expr->identificationVariable.'.'.$expr->field;

		if (!isset($hint[$index])) {
			return $sql;
		}

		$search = $this->walkPathExpression($expr).' '.$type;
		$sql = str_replace($search, $search.' '.$hint[$index], $sql);

		return $sql;
	}
}
