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

final class Replace extends FunctionNode
{
	public Node $column;
	public Node $from;
	public Node $to;


	public function parse(Parser $parser): void
	{
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);

		$this->column  = $parser->StringPrimary();

		$parser->match(Lexer::T_COMMA);

		$this->from = $parser->StringPrimary();
	
		$parser->match(Lexer::T_COMMA);

		$this->to = $parser->StringPrimary();

		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}


	public function getSql(SqlWalker $sqlWalker): string
	{
		return sprintf('REPLACE(%s, %s, %s)',
			$this->stringPrimary->dispatch($sqlWalker),
			$this->from->dispatch($sqlWalker),
			$this->to->dispatch($sqlWalker)
		);
	}
}
