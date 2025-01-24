<?php

/**
 * Author: Dennis Schwab - 2025
 * Licence: GPL2.0 or later
 * Version: 0.1.0
 */

declare(strict_types=1);

namespace DS\fluidHelpers\Utility;

use Exception;

final class MathematicalExpressionsException extends Exception {}

/**
 * Lex, parse and compile a mathematical string expression.
 * Use: $me = new MathematicalExpressions(); $res = $me->compileExpression('50+((-43*5)/3*2+8-3));
 */
final class MathematicalExpressions
{
    private $numbers = [];
    private $operators = [];

    public const RETURN_FLOAT = 1;
    public const RETURN_INT = 2;
    public const RETURN_STRING = 3;

    private function _setCurrentNumber(&$currentNumber, &$numberPrefix, &$decimalPointFound, &$decimalRadix)
    {
        $decimalPointFound = false;
        $decimalRadix = 10;
        if ($currentNumber !== NULL)
        {
            $this->numbers[] = $numberPrefix === '-' ? (0 - $currentNumber) : $currentNumber;
            $numberPrefix = NULL;
            $currentNumber = NULL;
        }
    }

    private function _parseAndCompilePartial()
    {
        /**
         * Calculate multiplications and divisions first
         */
        $exitLoop = false;
        $len = sizeof($this->operators);
        if ($len >= sizeof($this->numbers)) throw new MathematicalExpressionsException('Expression syntax error. Count of operators is equal or greater than numbers.');

        for ($i=0; $i < $len; $i++)
        {
            if (!isset($this->operators[$i]) || $exitLoop) break;
            switch ($this->operators[$i])
            {
                case '*':
                    $this->numbers[$i] *= $this->numbers[$i+1];
                    array_splice($this->numbers, ($i+1), 1);
                    array_splice($this->operators, $i, 1);
                    $i--;
                    break;
                case '/':
                    $this->numbers[$i] /= $this->numbers[$i+1];
                    array_splice($this->numbers, ($i+1), 1);
                    array_splice($this->operators, $i, 1);
                    $i--;
                    break;
                case '+':
                case '-':
                    break;
                default:
                    # Uh-oh, something's wrong here - terminate!
                    $exitLoop = true;
                    break;
            }
        }

        /**
         * Finalize additions and subtractions
         */
        $len = sizeof($this->operators);
        if ($len >= sizeof($this->numbers)) throw new MathematicalExpressionsException('Expression syntax error. Count of operators is equal or greater than numbers.');
        $exitLoop = false;
        while (sizeof($this->operators))
        { 
            if ($exitLoop) break;
            switch ($this->operators[0])
            {
                case '+':
                    $this->numbers[0] += $this->numbers[1];
                    array_splice($this->numbers, 1, 1);
                    array_splice($this->operators, 0, 1);
                    break;
                case '-':
                    $this->numbers[0] -= $this->numbers[1];
                    array_splice($this->numbers, 1, 1);
                    array_splice($this->operators, 0, 1);
                    $i--;
                    break;
                default:
                    $exitLoop = true;
                    break;
            }
        }

         return strval($this->numbers[0]);
    }

    private function _cleanExpression(&$expression)
    {
        $matches = [];
        preg_match_all("/(\([+\-]?[0-9]*\))/", $expression, $matches);
        foreach ($matches[0] as $match)
        {
            $expression = str_replace($match, trim($match, "()"), $expression);
        }
    }

    private function _lexPartial(string $expression)
    {
        $len = strlen($expression);
        $this->operators = [];
        $this ->numbers = [];
        $numberPrefix = NULL;
        $currentNumber = NULL;
        $decimalPointFound = false;
        $decimalRadix = 10;
        $radix = 10;
        for ($i=0; $i < $len; $i++)
        {
            switch ($expression[$i])
            {
                case '+':
                    $this->_setCurrentNumber($currentNumber, $numberPrefix, $decimalPointFound, $decimalRadix);
                    if ($i == 0 || !is_numeric($expression[$i-1]))
                    {
                        $numberPrefix = '+';
                    }
                    else
                    {
                        $this->operators[] = '+';
                    }
                    break;
                case '-':
                    $this->_setCurrentNumber($currentNumber, $numberPrefix, $decimalPointFound, $decimalRadix);
                    if ($i == 0 || !is_numeric($expression[$i-1]))
                    {
                        $numberPrefix = '-';
                    }
                    else
                    {
                        $this->operators[] = '-';
                    }
                    break;
                case '*':
                    $this->_setCurrentNumber($currentNumber, $numberPrefix, $decimalPointFound, $decimalRadix);
                    $this->operators[] = '*';
                    break;
                case '/':
                    $this->_setCurrentNumber($currentNumber, $numberPrefix, $decimalPointFound, $decimalRadix);
                    $this->operators[] = '/';
                    break;
                default:
                    if (is_numeric($expression[$i]))
                    {
                        if ($currentNumber === NULL)
                        {
                            $currentNumber = intval($expression[$i]);
                        }
                        else
                        {
                            if ($decimalPointFound === false)
                            {
                                $currentNumber *= $radix;
                                $currentNumber += intval($expression[$i]);
                            }
                            else
                            {
                                $tempValue = intval($expression[$i]);
                                $tempValue /= $decimalRadix;
                                $decimalRadix *= 10;
                                $currentNumber += $tempValue;
                            }
                        }
                    }
                    else if ($expression[$i] === '.')
                    {
                        if ($currentNumber === NULL)
                        {
                            $currentNumber = 0;
                            $decimalPointFound = true;
                        }
                        else
                        {
                            if ($decimalPointFound === true)
                            {
                                throw new MathematicalExpressionsException('Invalid expression syntax. Multiple decimal points found in number');
                            }
                            else
                            {
                                $decimalPointFound = true;
                            }
                        }
                    }
                    break;
            }
        }
        $this->_setCurrentNumber($currentNumber, $numberPrefix, $decimalPointFound, $decimalRadix);
    }
    
    /**
     * Converts and calculates a single string expression to the desired output type.
     * 
     * @param string $expression The mathematical string expression, which supports the four standard math operators, parantheses and
     * dot operations before line operations. Furthermore the middle dot · (multiplication) and the ratio : (division) are supported.
     * @param array $mergeFields A map of variables in format ["variableName" => value, ...]
     * @param int $returnType Determines of the return value shall have the type string, float or int. Can be set by the constants 
     * MathematicalExpressions::RETURN_STRING, MathematicalExpressions::RETURN_FLOAT and MathematicalExpressions::RETURN_INT
     * 
     * @return mixed The compiled expression. Type can be string, float or int. Defined by the constants 
     * MathematicalExpressions::RETURN_STRING, MathematicalExpressions::RETURN_FLOAT and MathematicalExpressions::RETURN_INT
     */
    public function compileExpression(string $expression, array $mergeFields = [], ?int $returnType = MathematicalExpressions::RETURN_STRING): mixed
    {
        if (!empty($mergeFields))
        {
            foreach ($mergeFields as $var => $val)
            {
                if (!is_numeric($val)) throw new MathematicalExpressionsException("Value $val is not a number. Variable: $var");
                $expression = str_replace($var, $val, $expression);
            }
        }

        /**
         * Replace common alternative math operators
         */
        $expression = str_replace('·', '*', str_replace(':', '/', $expression));

        /**
         * Validate the expression
         */

        $validate = [];
        if (preg_match_all("/(\[^0-9()*\-\/+ \])+/", $expression, $validate))
            throw new MathematicalExpressionsException("Expression contains non-mathematical characters.");

        /**
         * Remove all whitespaces
         */
        $expression = preg_replace('/[ \n\r]+/', '', $expression);
        /**
         * Clean from trash input
         */
        $this->_cleanExpression($expression);


        $partialExpressions = []; 
        /**
         * First extract and calculate all nested expressions wrapped by parentheses
         */
        while (preg_match_all("/\(([+\-]?[0-9]+[+\-*\/]{1}[+\-]?[0-9]*[.]*[0-9]+)+\)/", $expression, $partialExpressions))
        {
            $len = sizeof($partialExpressions[0]);
            for ($i=0; $i < $len; $i++)
            {
                $this->_lexPartial($partialExpressions[0][$i]);
                $expression = str_replace($partialExpressions[0][$i], $this->_parseAndCompilePartial(), $expression);
            }
        }
        /**
         * Finalize the calculation
         */
        $this->_lexPartial($expression);
        return $returnType === MathematicalExpressions::RETURN_INT ? intval($this->_parseAndCompilePartial()) :
            ($returnType === MathematicalExpressions::RETURN_FLOAT ? floatval($this->_parseAndCompilePartial()) :
            $this->_parseAndCompilePartial());
    }

    /**
     * Converts and calculates multiple string expression to the desired output type.
     * 
     * @param string $expression The mathematical string expression, which supports the four standard math operators, parantheses and
     * dot operations before line operations. Furthermore the middle dot · (multiplication) and the ratio : (division) are supported.
     * @param array $mergeFields A map of variables in format ["variableName" => value, ...]
     * @param int $returnType Determines of the return value shall have the type string, float or int. Can be set by the constants 
     * MathematicalExpressions::RETURN_STRING, MathematicalExpressions::RETURN_FLOAT and MathematicalExpressions::RETURN_INT
     * @param string $expressions Further expressions. Same conditions as for the $expression parameter.
     * 
     * @return array The compiled expressions in the same order as the inputs ($expression, $expressions...).
     * Type can be string, float or int. Defined by the constants 
     * MathematicalExpressions::RETURN_STRING, MathematicalExpressions::RETURN_FLOAT and MathematicalExpressions::RETURN_INT
     */
    public function compileExpressions (
        string $expression, ?array $mergeFields = [], ?int $returnType = MathematicalExpressions::RETURN_STRING, ?string ...$expressions
    ): array
    {
        $_expressions = [];
        $_expressions[] = $this->compileExpression($expression, $mergeFields, $returnType);
        foreach ($expressions as $_expression)
        {
            $_expressions[] = $this->compileExpression($_expression, $mergeFields, $returnType);
        }
        return $_expressions;
    }
}

?>