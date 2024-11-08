<?php

namespace App\Extensions\Doctrine;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\Lexer;

class MatchAgainst extends FunctionNode
{
    protected $against;

    protected $booleanMode = false;

    /**
     * @var array
     */
    public $columns = [];

    /**
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $haystack = null;
        $first = true;

        foreach ($this->columns as $column) {
            $first ? $first = false : $haystack .= ', ';
            $haystack .= $column->dispatch($sqlWalker);
        }

        $query = 'MATCH(' . $haystack . ') AGAINST (' . $sqlWalker->walkStringPrimary($this->against);

        if ($this->booleanMode) {
            $query .= ' IN BOOLEAN MODE';
        }

        $query .= ' )';

        return $query;
    }

    /**
     * @param \Doctrine\ORM\Query\Parser $parser
     *
     * @return void
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->columns = [];
        $this->columns[] = $parser->StateFieldPathExpression();

        while ($parser->getLexer()->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->columns[] = $parser->StateFieldPathExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);

        if (strtolower((string) $parser->getLexer()->lookahead['value']) !== 'against') {
            $parser->syntaxError('against');
        }

        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->against = $parser->StringPrimary();

        if (strtolower((string) $parser->getLexer()->lookahead['value']) === 'boolean') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->booleanMode = true;
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}