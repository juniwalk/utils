<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * "cast" "(" Column, Type ")")
 */
final class Cast extends FunctionNode
{
	public Node $column;
	public Node $type;


	public function parse(Parser $parser): void
	{
		$parser->match(Lexer::T_IDENTIFIER); // (2)
		$parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)

		$this->column = $parser->StringPrimary(); // (4)

		$parser->match(Lexer::T_COMMA); // (5)

		$this->type = $parser->StringPrimary(); // (6)

		$parser->match(Lexer::T_CLOSE_PARENTHESIS); // (7)
	}


	/**
	 * @see https://github.com/oroinc/doctrine-extensions/blob/master/src/Oro/ORM/Query/AST/Platform/Functions/Postgresql/Cast.php
	 */
	public function getSql(SqlWalker $sqlWalker): string
	{
        /** @var Node $value */
		$column = $sqlWalker->walkSimpleArithmeticExpression($this->column);
		$type = $sqlWalker->walkSimpleArithmeticExpression($this->type);

        $type = trim(strtolower($type), '"\'');
        if ($type === 'datetime') {
			return '"timestamp"(' . $column . ')';
        }

        if ($type === 'json' && !$sqlWalker->getConnection()->getDatabasePlatform()->hasNativeJsonType()) {
            $type = 'text';
        }

        if ($type === 'bool') {
            $type = 'boolean';
        }

        if ($type === 'binary') {
            $type = 'bytea';
        }

        /**
         * The notations varchar(n) and char(n) are aliases for character varying(n) and character(n), respectively.
         * character without length specifier is equivalent to character(1). If character varying is used
         * without length specifier, the type accepts strings of any size. The latter is a PostgreSQL extension.
         * http://www.postgresql.org/docs/9.2/static/datatype-character.html
         */
        if ($type === 'string') {
            $type = 'varchar';
        }

        return 'cast(' . $column . ' AS ' . $type . ')';
	}
}
