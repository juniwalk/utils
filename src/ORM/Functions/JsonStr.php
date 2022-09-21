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
 * "json_str" "(" Column, Property ")"
 */
final class JsonStr extends FunctionNode
{
	public Node $property;
	public Node $column;


	public function parse(Parser $parser): void
	{
		$parser->match(Lexer::T_IDENTIFIER); // (2)
		$parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)

		$this->column = $parser->StringPrimary(); // (4)

		$parser->match(Lexer::T_COMMA); // (5)

		$this->property = $parser->SimpleArithmeticExpression(); // (6)

		$parser->match(Lexer::T_CLOSE_PARENTHESIS); // (7)
	}


	public function getSql(SqlWalker $sqlWalker): string
	{
		$property = $sqlWalker->walkSimpleArithmeticExpression($this->property);
		$column = $sqlWalker->walkSimpleArithmeticExpression($this->column);

		return "{$column}->>{$property}";
	}
}
