<?php

/**
 * Author: Dennis Schwab - 2025
 * Licence: GPL2.0 or later
 * Version: 0.1.0
 */

declare(strict_types=1);

namespace DS\fluidHelpers\Utility;

use Doctrine\DBAL\Query\QueryBuilder;
use DS\fluidHelpers\ViewHelpers\SetViewHelper;
use Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use __IDE\Pure;

class SimpleDatabaseQueryException extends Exception {}

final class SimpleDatabaseQueryNodes extends SimpleDatabaseQueryNode
{
    /** @var array<SimpleDatabaseQueryNode> $nodes */
    protected array $nodes;

    public function __construct()
    {
        $this->nodes = [];
    }

    public function addNode(SimpleDatabaseQueryNode $node, int $index)
    {
        $type = $node->getType();
        switch ($type)
        {
            case SimpleDatabaseQueryNode::CLAUSE:
                $lastNode = $this->sizeof() > 0 ? Utility::array_last($this->nodes) : NULL;
                if ($lastNode && $lastNode->getType() === SimpleDatabaseQueryNode::CLAUSE)
                    throw new SimpleDatabaseQueryException("Syntax error in expression: Found clause operator at wrong position: $index.");
                if ($node->getLValue() === '')
                    throw new SimpleDatabaseQueryException("No column name is set at position: $index.");
                else if ($node->getComparison() === NULL)
                    throw new SimpleDatabaseQueryException("No comparison operator is set at position: $index.");
                else if ($node->getRValue() === NULL || $node->getRValue() === '')
                    throw new SimpleDatabaseQueryException("No value is set at position: $index.");
                break;
            case SimpleDatabaseQueryNode::LOGICAL:
                if ($this->sizeof() == 0)
                    throw new SimpleDatabaseQueryException("Expression can't start with logical operators.");
                $lastNode = Utility::array_last($this->nodes);
                if ($lastNode && $lastNode->getType() === SimpleDatabaseQueryNode::LOGICAL)
                    throw new SimpleDatabaseQueryException("Syntax error in expression: Found logical operator at wrong position: $index.");
                break;
            case SimpleDatabaseQueryNode::GROUP:
                break;
            case SimpleDatabaseQueryNode::COMMA_GROUP:
                break;
            default:
                throw new SimpleDatabaseQueryException("Unknown error. Please verify that the following value ranges between 1 and 4: $type.");
                break;
        }

        $this->nodes[] = $node;
    }

    #[Pure]
    public function getNodes()
    {
        return $this->nodes;
    }

    #[Pure]
    public function sizeof()
    {
        return sizeof($this->nodes);
    }
}

class SimpleDatabaseQueryNode
{
    public const CLAUSE = 1;
    public const LOGICAL = 2;
    public const GROUP = 3;
    public const COMMA_GROUP = 4;

    public function __construct
    (
        protected string $lValue,
        protected ?int $type = SimpleDatabaseQueryNode::CLAUSE,
        protected ?string $comparison = NULL,
        protected mixed $rValue = NULL
    ){}

    #[Pure]
    public function getType()
    {
        return $this->type;
    }

    #[Pure]
    public function getLValue()
    {
        return $this->lValue;
    }

    #[Pure]
    public function getComparison()
    {
        return $this->comparison;
    }

    #[Pure]
    public function getRValue()
    {
        return $this->rValue;
    }

    public function setLValue($lValue)
    {
        $this->lValue = $lValue;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setComparison($comparison)
    {
        $this->comparison = $comparison;
    }

    public function setRValue($rValue)
    {
        $this->rValue = $rValue;
    }
}

/**
 * Fetch data from a table in the database.
 * Usage:
 * 
 * $sqd = new SimpleDatabaseQuery();
 * 
 * if($sdq->tableExists('products') {
 * 
 *  $res = $sqd->fetch('products', '*', 'stock >= 1 && category == $category && (title %% 'screw' || title %% 'nail')', 'price ASC',
 *  ['category' => 1], false, false);
 * 
 *  ...
 * 
 * }
 */
final class SimpleDatabaseQuery
{
    protected QueryBuilder $queryBuilder;
    protected SimpleDatabaseQueryNodes $clauses;
    protected ?SimpleDatabaseQueryNode $currentNode;
    protected array $variables;
    protected int $groupCount;
    protected bool $isColumn;
    protected string $isString;
    protected bool $currentVarIsString;
    protected bool $isLike;
    protected bool $isIn;

    public function __construct
    (
        protected ?ConnectionPool $connection = NULL
    )
    {
        $this->connection = $this->connection ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }

    private function _establishConnection($table): QueryBuilder
    {
        return $this->connection->getQueryBuilderForTable($table);
    }

    private function _newQueryNode(string $lValue, ?int $type = SimpleDatabaseQueryNode::CLAUSE): void
    {
        if (!$this->currentNode)
        {
            $this->currentNode = new SimpleDatabaseQueryNode($lValue, $type);
        }
    }

    private function _addQueryNode($index): void
    {
        $this->clauses->addNode($this->currentNode, $index);
        $this->currentNode = NULL;
    }

    private function _convertValue(&$value): mixed
    {
        if (is_numeric($value))
        {
            if (str_contains($value, '.'))
                $value = floatval($value);
            else
                $value = intval($value);
        } else if (is_string($value)) {
            if (strtolower($value) === 'true')
                $value = 1;
            else if (strtolower($value) === 'false')
                $value = 0;
        }
        return $value;
    }

    private function _addComparisonOperator($operator, $expression, &$index, $len, &$column, &$value): void
    {
        if (!$this->isString)
        {
            if ($index == $len)
                throw new SimpleDatabaseQueryException("Syntax error in expression. Wrong terminating character $operator.");
            $this->isColumn = false;
            $this->_newQueryNode($column);
            $column = '';
            $index++;
            switch ($operator)
            {
                case '<':
                    if ($expression[$index] === '=')
                    {
                        $this->currentNode->setComparison('<=');
                    }
                    else
                    {
                        $this->currentNode->setComparison('<');
                        $index--;
                    }
                    break;
                case '>':
                    if ($expression[$index] === '=')
                    {
                        $this->currentNode->setComparison('>=');
                    }
                    else
                    {
                        $this->currentNode->setComparison('>');
                        $index--;
                    }
                    break;
                case '=':
                    if ($expression[$index] !== '=')
                        throw new SimpleDatabaseQueryException("Syntax error in expression. Wrong comparison character at position $index.");
                    $this->currentNode->setComparison('==');
                    break;
                case '!':
                    if ($expression[$index] !== '=')
                        throw new SimpleDatabaseQueryException("Syntax error in expression. Wrong comparison character at position $index.");
                    $this->currentNode->setComparison('!=');
                    break;
                case '%':
                    if ($expression[$index] !== '%')
                        throw new SimpleDatabaseQueryException("Syntax error in expression. Wrong comparison character at position $index.");
                    $this->currentNode->setComparison('%%');
                    $this->isLike = true;
                    break;
                case '-':
                    if ($expression[$index] !== '>')
                        throw new SimpleDatabaseQueryException("Syntax error in expression. Wrong comparison character at position $index.");
                    $this->currentNode->setComparison('->');
                    $this->isIn = true;
                    break;
                default:
                    throw new SimpleDatabaseQueryException("Unknown error. Wrong operand $operator is forwarded to function _addCompareOperand");
                    break;
            }
            
        }
        else
        {
            if ($this->isColumn) $column .= $operator;
            else $value .= $operator;
        }
    }

    private function _addValueToClause($expression, $index, $isLast, &$value): void
    {
        if ($this->isColumn)
        {
            $index++;
            throw new SimpleDatabaseQueryException (
                "Unknown error in syntax. Value is marked as a column name at expression character position $index. " .
                "Have you set a variable?"
            );
        }

        if (is_string($value)) {
            $value = htmlspecialchars($value);
        }

        if ($this->isString)
        {
            if (!$isLast) $index--;
            if ($expression[$index] !== '"' && $expression[$index] !== "'")
                throw new SimpleDatabaseQueryException (
                    "Error in syntax: No string termination character before end of expression or logical operator at position $index."
                );
            if ($expression[$index] !== $this->isString)
            {
                $closingCharacter = $expression[$index];
                throw new SimpleDatabaseQueryException (
                    "Error in syntax: String termination characters do not match. Opening character is $this->isString " .
                    "and closing character is $closingCharacter at position $index."
                );
            }   
        }
        else
        {
            if ($this->currentVarIsString)
            {
                $value = "'" . $value . "'";
                $this->currentVarIsString = false;
            }
            else $value = $this->_convertValue($value);
        }

        if (is_string($value)) {
            if ($value !== '' && $this->isLike) $value = '%' . $value . '%';
            if ($value !== '')
            {
                $this->currentNode->setRValue($value);
                $this->_addQueryNode($index);
            }
        } else if ($value instanceof SimpleDatabaseQueryNode) {
            if ($value->getType() === SimpleDatabaseQueryNode::COMMA_GROUP) {
                $value->setLValue(trim($value->getLValue(), '()'));
                $splittedValues = Utility::stringSafeTrimExplide(',', $value->getLValue());
                $len = count($splittedValues);
                for ($i=0; $i < $len; $i++) { 
                    $splittedValues[$i] = "'" . trim($splittedValues[$i], "'") . "'";
                }
                $value->setLValue(implode(', ', $splittedValues));
                $this->currentNode->setRValue($value);
                $this->_addQueryNode($index);
            }
        }

        $value = '';
        $this->isColumn = true;
    }

    private function _addLogicalOperator($operator, $expression, &$index, $len, &$column, &$value): void
    {
        if (!$this->isString)
        {
            if ($index == $len)
                    throw new SimpleDatabaseQueryException("Syntax error in expression. Wrong terminating character $operator.");
            $index++;
            if ($value !== '') $this->_addValueToClause($expression, $index, false, $value);
            switch ($operator)
            {
                case '&':
                    if ($expression[$index] !== '&')
                        throw new SimpleDatabaseQueryException("Syntax error in expression. Wrong logical character at position $index.");
                    $this->_newQueryNode('&&', SimpleDatabaseQueryNode::LOGICAL);
                    $this->_addQueryNode($index);
                    break;
                case '|':
                    if ($expression[$index] !== '|')
                        throw new SimpleDatabaseQueryException("Syntax error in expression. Wrong logical character at position $index.");
                    $this->_newQueryNode('||', SimpleDatabaseQueryNode::LOGICAL);
                    $this->_addQueryNode($index);
                    break;
                default:
                    throw new SimpleDatabaseQueryException("Unknown error. Wrong operator $operator is forwarded to function _addLogicalOperator");
                    break;
            }
        }
        else
        {
            if ($this->isColumn) $column .= $operator;
            else $value .= $operator;
        }
    }

    private function _finalizeLexAndParse($expression, $index, $value): void
    {
        $finalChar = $expression[$index];
        if ($this->isString)
        {
            if ($finalChar !== '"' && $finalChar !== "'")
                throw new SimpleDatabaseQueryException("Syntax error in expression: No closing string character found.");
            if ($expression[$index-1] === '\\' && Utility::isEscaped($expression, $index-1))
                throw new SimpleDatabaseQueryException("Syntax error in expression: Last closing string character is escaped.");

            if ($this->currentVarIsString) $this->isString = '';
            $this->_addValueToClause($expression, $index, true, $value);
        }
        else
        {
            if ($this->groupCount == 1 && $finalChar === ')') $this->_addGroup($expression, $index, $value, $column, false);
            else
            {
                $value .= $finalChar;
                $this->_addValueToClause($expression, $index, true, $value);
            }
        }
        if ($this->groupCount != 0)
        {
            if ($this->groupCount > 0)
            {
                throw new SimpleDatabaseQueryException("Number of group characters do not match. Missing $this->groupCount closing ) characters.");
            }
            else
            {
                throw new SimpleDatabaseQueryException("Number of group characters do not match. Missing $this->groupCount opening ( characters.");
            }
        }
    }

    private function _addGroup($expression, $index, &$value, &$column, ?bool $open = true): void
    {
        $char = $open ? '(' : ')';
        if (!$this->isString)
        {
            if (!$this->isColumn && $value !== '') $this->_addValueToClause($expression, $index, false, $value);
            $this->_newQueryNode($char, SimpleDatabaseQueryNode::GROUP);
            $this->_addQueryNode($index);
            $this->groupCount += $open ? 1 : -1;
        }
        else
        {
            if ($this->isColumn) $column .= $char;
            else $value .= $char;
        }
    }

    private function _addCommaGroup($expression, &$index, int $lenMinusOne, &$value, &$column, ?bool $open = true): void
    {
        $group = '';
        if (!$this->isString)
        {
            $isString = '';
            $breakOut = false;
            for (; $index < $lenMinusOne && !$breakOut; $index++) { 
                $char = $expression[$index];
                switch ($char) {
                    case '\'':
                        if (($expression[$index-1] !== '\\') || ($expression[$index-1] === '\\' && !Utility::isEscaped($expression, ($index-1)))) {
                            if ($isString === '') {
                                $isString = '\'';
                            } else if ($isString === '\'') {
                                $isString = '';
                            }
                        }
                        break;
                    case '"':
                        if (($expression[$index-1] !== '\\') || ($expression[$index-1] === '\\' && !Utility::isEscaped($expression, ($index-1)))) {
                            if ($isString === '') {
                                $isString = '"';
                            } else if ($isString === '"') {
                                $isString = '';
                            }
                        }
                        break;
                    case ')':
                        if ($isString === '') {
                            $breakOut = true;
                            $index--;
                        }
                        $group .= ')';
                        break;
                    default:
                        $group .= $char;
                        break;
                }
            }
            $value = new SimpleDatabaseQueryNode($group, SimpleDatabaseQueryNode::COMMA_GROUP);
            $this->_addValueToClause($expression, $index, false, $value);
        } else {
            if ($this->isColumn) $column .= $open ? '(' : ')';
            else $value .= $open ? '(' : ')';
        }
    }

    private function _lexAndParseWhereClauses($clause): void
    {
        $expression = str_replace(' ', '', str_replace('IN', '->', $clause));
        if ($expression === '') return;
        $len = strlen($expression);
        $lenMinusOne = $len-1;
        $column = '';
        $value = '';
        $this->clauses = new SimpleDatabaseQueryNodes();
        $this->currentNode = NULL;
        $this->groupCount = 0;
        $this->isColumn = true;
        $this->isString = '';
        $this->currentVarIsString = false;
        $this->isLike = false;
        $this->isIn = false;
        for ($i=0; $i < $lenMinusOne; $i++)
        {
            $char = $expression[$i];
            switch ($char)
            {
                case '\'':
                    if ($i > 0 && (($expression[$i-1] !== '\\') || ($expression[$i-1] === '\\' && !Utility::isEscaped($expression, ($i-1)))))
                    {
                        if ($this->isString === '' && !$this->isColumn)
                        {
                            $this->isString = "'";
                            $this->currentVarIsString = true;
                        }
                        else $this->isString = '';
                    }
                    break;
                case '"':
                    if ($i > 0 && (($expression[$i-1] !== '\\') || ($expression[$i-1] === '\\' && !Utility::isEscaped($expression, ($i-1)))))
                    {
                        if ($this->isString === '' && !$this->isColumn)
                        {
                            $this->isString = "'";
                            $this->currentVarIsString = true;
                        }
                        else $this->isString = '';
                    }
                    break;
                case '(':
                    if (!$this->isIn) {
                        $this->_addGroup($expression, $i, $value, $column);
                    } else {
                        $this->_addCommaGroup($expression, $i, $lenMinusOne, $value, $column);
                    }
                    break;
                case ')':
                    if (!$this->isIn) {
                        $this->_addGroup($expression, $i, $value, $column, false);
                    } else {
                        $this->isIn = false;
                    }
                    break;
                case '<':
                    $this->_addComparisonOperator('<', $expression, $i, $len, $column, $value);
                    break;
                case '>':
                    $this->_addComparisonOperator('>', $expression, $i, $len, $column, $value);
                    break;
                case '=':
                    $this->_addComparisonOperator('=', $expression, $i, $len, $column, $value);
                    break;
                case '!':
                    $this->_addComparisonOperator('!', $expression, $i, $len, $column, $value);
                    break;
                case '%':
                    $this->_addComparisonOperator('%', $expression, $i, $len, $column, $value);
                    break;
                case '-':
                    $this->_addComparisonOperator('-', $expression, $i, $len, $column, $value);
                    break;
                case '&':
                    $this->_addLogicalOperator('&', $expression, $i, $len, $column, $value);
                    break;
                case '|':
                    $this->_addLogicalOperator('|', $expression, $i, $len, $column, $value);
                    break;
                default:
                    if ($this->isColumn) $column .= $char;
                    else $value .= $char;
                    break;
            }
        }
        $this->_finalizeLexAndParse($expression, $lenMinusOne, $value);
    }

    private function _injectVariable(SimpleDatabaseQueryNode &$node)
    {
        if ($node->getType() === SimpleDatabaseQueryNode::CLAUSE)
        {
            $rValue = $node->getRValue();
            if (is_string($rValue) && $rValue !== '')
            {
                if ($rValue[0] === '$')
                {
                    $rValue = substr($rValue, 1);
                    if (array_key_exists($rValue, $this->variables))
                    {
                        $node->setRValue($this->_convertValue(htmlspecialchars($this->variables[$rValue])));
                    }
                }
            }
            else if ($rValue === '' || $rValue === NULL)
            {
                $column = $node->getLValue();
                if ($column)
                    throw new SimpleDatabaseQueryException("No value set for column name $column.");
                else
                    throw new SimpleDatabaseQueryException("An clause has neither a column name nor a value.");
            }
        }
    }

    private function _buildWhere(): string
    {
        $nodes = $this->clauses->getNodes();
        $dql = '';
        foreach ($nodes as $node)
        {
            $type = $node->getType();
            switch ($type)
            {
                case SimpleDatabaseQueryNode::CLAUSE:
                    $dql .= $node->getLValue() . ' ';
                    switch ($node->getComparison())
                    {
                        case '==':
                            $dql .= '= ';
                            break;
                        case '%%':
                            $dql .= 'LIKE ';
                            break;
                        case '->':
                            $dql .= 'IN ';
                            break;
                        default:
                            $dql .= $node->getComparison() . ' ';
                            break;
                    }
                    $this->_injectVariable($node);
                    
                    if (is_string($node->getRValue())) {
                        $dql .= $node->getRValue() . ' ';
                    } else {
                        $dql .= '(' . $node->getRValue()->getLValue() . ') ';
                    }
                    
                    break;
                case SimpleDatabaseQueryNode::LOGICAL:
                    $logicalOperator = $node->getLValue();
                    switch ($logicalOperator)
                    {
                        case '&&':
                            $dql .= 'AND ';
                            break;
                        case '||':
                            $dql .= 'OR ';
                            break;
                        default:
                            throw new SimpleDatabaseQueryException("Unsupported logical operator $logicalOperator in expression");
                            break;
                    }
                    break;
                case SimpleDatabaseQueryNode::GROUP:
                    $dql .= $node->getLValue();
                    break;
                default:
                    throw new SimpleDatabaseQueryException("Unknown error. Please verify that the follwing value ranges between 1 and 3: $type.");
                    break;
            }
        }
        return $dql;
    }

    private function _createOrderBy($order): void
    {
        $matches = [];
        preg_match_all('/([^ \n\r,]+ +(ASC|asc|DESC|desc))/', $order, $matches, PREG_SET_ORDER);
        foreach ($matches as $match)
        {
            $match = explode(' ', $match[0]);
            if (is_array($match))
            {
                $columnName = trim($match[0]);
                $order = trim($match[1]);
                $this->queryBuilder->addOrderBy($columnName, $order);
            }
        }
    }

    /**
     * Verifies if a table with the given identifier exists.
     * 
     * @param string $table The name of the table in the database.
     * 
     * @return bool Returns true if the table with the given identifier exists and false otherwise.
     */
    public function tableExists(string $table): bool
    {
        return $this->connection->getConnectionForTable($table)->createSchemaManager()->tableExists($table);
    }
    
    /**
     * Fetch data from a table in the database.
     *
     * @param string $table The identifier of the table to fetch the data from.
     * @param ?string $select You can reduce the output to specific columns or read all columns by simply forwarding * (Default: *).
     * @param ?string $whereClauseExpression The where clauses rely on the if-syntax of C-type languages with the exception of the added 
     * comparison operator %% . The operator %% stands for the LIKE keyword and wraps the value with percentage characters.
     * Typical where expressions could look like:
     * 
     * 1): category == 5 && (price < 20.0 || discount >= 20)
     * 
     * 2): city == 'New York' && street %% 'Avenue'
     * 
     * (Default: uid == 0)
     * 
     * @param ?string $order Comma-separated ordering definitions in the format COLUMN_NAME ASC|DESC.
     * 
     * E.g.: surname DESC, firstname ASC 
     * 
     * (Default: '')
     * 
     * @param ?array $variables You can pass an array of variables to the where expression in the format ['varName1' => value1, 'varName2' => value2].
     * 
     * These will be injected to merge fields in the expression. A merge field can be declared like a PHP variable with a preceding $ sign.
     * 
     * An example usage could be: $variables = ['name' => 'Jack'], $whereClauseExpression = 'age > 18 && firstname == $name'
     * 
     * (Default: [])
     * 
     * @param ?bool $useNativeDqlSyntax The if-syntax is capable of taking basic instructions which usually cover most cases. If you need
     * more complex expressions with more operators though, you can pass Doctrine's DQL language instead if this value is set to true.
     * (Default: false)
     * 
     * @param ?bool $throwIfTableDoesNotExist If the existence of a table is crucial for the further process, you can set this parameter to
     * true to throw an exception if the table does not exist. (Default: true)
     *  
     * @return array An array of arrays of the fetched entries from the database.
    */
    public function fetch (
        string $table,
        string|array $select = '*',
        ?string $whereClauseExpression = 'uid == 0',
        ?string $order = '',
        ?array $variables = [],
        ?bool $useNativeDqlSyntax = false,
        ?bool $throwIfTableDoesNotExist = true
    ): array
    {
        if ($this->tableExists($table) || $throwIfTableDoesNotExist === false)
        {
            $this->variables = $variables;
            $select = is_array($select) ? $select : [$select];
            $this->queryBuilder = $this->_establishConnection($table);
            $this->_lexAndParseWhereClauses($whereClauseExpression);
            $this->queryBuilder
                ->select(...$select)
                ->from($table)
                ->where($useNativeDqlSyntax ? $whereClauseExpression : $this->_buildWhere());
            $this->_createOrderBy($order);
           return $this->queryBuilder
                ->executeQuery()
                ->fetchAllAssociative();
        }
        else
        {
            if ($throwIfTableDoesNotExist)
                throw new SimpleDatabaseQueryException("Table with identifier $table does not exist.");
            else return [];
        }
    }

    public static function getFieldProperties(string $identifier, ?string $table = ''): array
    {
        $table = $table ?? 'tt_content';
        $sdq = new SimpleDatabaseQuery();
        if ($sdq->tableExists($table)) {
            $select = [
                'TABLE_SCHEMA', 'TABLE_NAME', 'COLUMN_NAME', 'ORDINAL_POSITION', 'COLUMN_DEFAULT', 'IS_NULLABLE', 'DATA_TYPE',
                'CHARACTER_MAXIMUM_LENGTH', 'CHARACTER_OCTET_LENGTH', 'NUMERIC_PRECISION', 'NUMERIC_SCALE', 'DATETIME_PRECISION',
                'CHARACTER_SET_NAME', 'COLLATION_NAME', 'COLUMN_TYPE', 'COLUMN_KEY', 'EXTRA', 'PRIVILEGES', 'COLUMN_COMMENT',
                'GENERATION_EXPRESSION'
            ];
            $where = "TABLE_NAME == '$table' && COLUMN_NAME == '$identifier'";

            return $sdq->fetch("INFORMATION_SCHEMA.COLUMNS", $select, $where, '', [], false, false);
        }
    }

    public static function getFieldsProperties(string $identifier, ?array $tables = ['*']): array
    {
        $sdq = new SimpleDatabaseQuery();
        $select = [
            'TABLE_SCHEMA', 'TABLE_NAME', 'COLUMN_NAME', 'ORDINAL_POSITION', 'COLUMN_DEFAULT', 'IS_NULLABLE', 'DATA_TYPE',
            'CHARACTER_MAXIMUM_LENGTH', 'CHARACTER_OCTET_LENGTH', 'NUMERIC_PRECISION', 'NUMERIC_SCALE', 'DATETIME_PRECISION',
            'CHARACTER_SET_NAME', 'COLLATION_NAME', 'COLUMN_TYPE', 'COLUMN_KEY', 'EXTRA', 'PRIVILEGES', 'COLUMN_COMMENT',
            'GENERATION_EXPRESSION'
        ];
        $tableNames = '';
        if (!in_array('*', $tables)) {
            $tableNames .= 'TABLE_NAME IN (';
            $legit = false;
            foreach ($tables as $table) {
                if (is_string($table) && $sdq->tableExists($table)) {
                    $tableNames .= "'" . trim($table, "'") . "', ";
                    $legit = true;
                }
            }
            if (!$legit) return [];
            $tableNames = trim($tableNames, ', ') . ') && ';
        }
        $where = $tableNames . "COLUMN_NAME == '$identifier'";
        return $sdq->fetch("INFORMATION_SCHEMA.COLUMNS", $select, $where, '', [], false, false);
    }

    public static function fieldExists(string $field, ?array $tables = ['*']): bool
    {
        return !empty(SimpleDatabaseQuery::getFieldsProperties($field, $tables));
    }

}

?>
