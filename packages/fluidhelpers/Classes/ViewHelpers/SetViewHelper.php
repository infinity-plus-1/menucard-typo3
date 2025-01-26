<?php

declare(strict_types=1);

namespace DS\fluidHelpers\ViewHelpers;

use DS\fluidHelpers\Utility\MathematicalExpressions;
use Exception;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class SetViewHelperException extends Exception {}

final class SetViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('variables', 'mixed', 'The variable to set to the global scope.', true);
        $this->registerArgument('mergeFields', 'mixed', 'If variable is a string or mathematical expression which contains other variables those can be forwarded here.');
        $this->registerArgument('isExpression', 'boolean', 'Set to true if the variable is a mathematical expression that shall get compiled', false, false);
        $this->registerArgument('force', 'boolean', 'If true existing keys will be overwritten. Otherwise an exception will be thrown.', false, false);
    }

    private function _isEscaped($str, $pos): bool
    {
        $count = 0;
        while ($pos-- >= 0 && $str[$pos] === '\\') $count++;
        return $count % 2 !== 0; 
    }
    
    private function _injectVariable($str, $identifier, $variable): string
    {
        $cIndex = 0;
        while ($cIndex = strpos($str, $identifier, $cIndex))
        {
            if ($cIndex > 0 && $str[$cIndex-1] === '$')
            {
                
                if ($cIndex > 1 && !$this->_isEscaped($str, ($cIndex-2)))
                {
                    $identifierLen = strlen($identifier) + $cIndex;
                    $str = substr($str, 0, ($cIndex-1)) . $variable . substr($str, $identifierLen, strlen($str));
                    $cIndex = $identifierLen;
                }
                else
                {
                    $identifierLen = strlen($identifier) + $cIndex;
                    $str = substr($str, 0, ($cIndex-1)) . '$' . substr($str, $identifierLen, strlen($str));
                    $cIndex = $identifierLen;
                }
            }
        }
        return $str;
    }

    private function _compileExpression($expression, $mergeFields): int|float
    {
        $expression = $this->_renderString($expression, $mergeFields);
        $me = new MathematicalExpressions();
        $value = $me->compileExpression($expression);
        return str_contains($value, '.') ? floatval($value) : intval($value);
    }

    private function _checkAndReturnValue($identifier, $value): mixed
    {
        if ($value === '$')
        {
            $globalVars = $this->renderingContext->getVariableProvider();
            if ($globalVars->exists($identifier)) return $globalVars->get($identifier);
            else return '';
        }
        else
        {
            if (is_string($value))
            {
                
                $len = strlen($value) - 1;
                if ($value[$len] === '$' && $len > 0 && $value[$len-1] === '\\' && $this->_isEscaped($value, ($len-1)))
                {
                    $offset = $len;
                    while ($offset > 0 && $value[$offset--] === '\\');
                    if ($offset > 0) $value = substr($value, 0, ($offset - 1)) . $value[$len];
                    else $value = '$';
                }

            }
            return $value;
        }
    }

    private function _renderString($variable, $mergeFields): string
    {
        
        if ($mergeFields && is_array($mergeFields))
        {
            foreach ($mergeFields as $identifier => $value)
            {
                if (is_array($value)) $value = json_encode($value);
                else if(is_numeric($value)) $value = strval($value);
                else if (is_object($value) && method_exists($value, "__toString")) $value = $value->__toString();
                else if (is_bool($value)) $value = strval($value);
                if (is_string($value)) $variable = $this->_injectVariable($variable, $identifier, $this->_checkAndReturnValue($identifier, $value));
            }
        }
        return $variable;
    }

    #[Pure]
    private function _nameValidator($name): bool
    {
        return in_array($name, [
            'settings', 'data', 'current'
        ]);
    }

    public function render(): void
    {
        $variables = $this->arguments['variables'] ?? NULL;
        if (!$variables) throw new SetViewHelperException('No variable set.');
        if (!is_array($variables)) throw new SetViewHelperException('Wrong variable declaration. Declare as follow: {variableName: variableValue, ...}');

        $mergeFields = $this->arguments['mergeFields'] ?? NULL;
        $isExpression = $this->arguments['isExpression'];

        $globalVars = $this->renderingContext->getVariableProvider();
        //dd($globalVars);
        foreach ($variables as $key => $variable)
        {
            if ($this->_nameValidator($key))
                throw new SetViewHelperException("Variable name '$key' is reserved. Please choose an other identifier.");

            if ($isExpression && !is_string($variable))
                throw new SetViewHelperException('The argument isExpression is set to true but the argument variable is not of type string.');
            else if($isExpression)
                $variable = $this->_compileExpression($variable, $mergeFields);
            else if (is_string($variable))
                $variable = $this->_renderString($variable, $mergeFields);

            if ($this->arguments['force'] && $globalVars->exists($key)) $globalVars->remove($key);

            
            $globalVars->add($key, $variable);
        }
        

        dump($GLOBALS['TCA']);
        
    }
}

?>