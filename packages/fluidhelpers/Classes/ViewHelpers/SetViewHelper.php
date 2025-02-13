<?php

declare(strict_types=1);

namespace DS\fluidHelpers\ViewHelpers;

use DS\fluidHelpers\Utility\MathematicalExpressions;
use DS\fluidHelpers\Utility\Utility;
use Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class SetViewHelperException extends Exception {}

/**
 * Add variable(s) to the global variable provider for use in FLUID.
 * 
 * Arguments:
 * @param mixed value Add one or more variables using key-value notation, separated by commas.
 * 
 * These pairs can be passed as an array using {}:  
 * <fh:set value="{key1: 'value1', key2: 'value2'}" />
 * 
 * Alternatively, they can be passed as a string by omitting the {}:  
 * <fh:set value="key1: 'value1', key2: 43" />
 * 
 * Keys can be pure integers or strings, with or without quotes.
 * 
 * Values can have any type if passed as an array. If provided as a string, they can be booleans, integers, floats, strings, arrays, or objects (in JSON format).
 * 
 * Values enclosed in single or double quotes are treated as strings, except when preceded by =, $, {, or [. More details on this later.
 * 
 * You can omit quotes unless using the words "true" or "false," numeric values like 9 or 9.0, or when using a comma or colon in the text.  
 * To avoid ambiguity, it is recommended to wrap strings in quotes.
 * 
 * Special Characters:
 * 
 * $: Indicates a merge field (placeholder), which will be replaced by a value if the same identifier exists in the mergeFields argument  
 * or in the global variable provider. (The mergeFields value takes priority if the identifier exists in both.)
 * 
 * {: If the string starts and ends with curly brackets, json_decode will be called on this variable.  
 * Ensure the JSON syntax is valid in this case.
 * 
 * [: Similar to {, but the JSON should represent a simple array.
 * 
 * =: Indicates a mathematical expression that will be computed and returned as a number (float or int).
 * 
 * Special characters do not need to be enclosed in quotes, as long as the conditions mentioned above are met.
 * 
 * To escape a special character, prepend it with \.
 * 
 * Examples:
 * 
 * $: <fh:set mergeFields="{placeholder: 'text'}">myVar: The following $placeholder will be replaced by the word 'text'.< /fh:set>
 * 
 * $: <fh:set value="globalVar: global variable" />  
 *    <fh:set value="myVar: This is a $globalVar." />
 * 
 * [: <fh:set value="arr: '[\\"City\\", \\"Country\\", \\"River\\"]'" />
 * 
 * {: <fh:set value="obj: '{ \\"name\\":\\"Jack\\", \\"age\\":43, \\"hobbies\\":[\\"fishing\\", \\"darts\\", \\"watching grass grow\\"] }'" />
 * 
 * =: <fh:set value="myExp: '= 43+74-(-32*8)+((45/15)*1000)'" />
 * 
 * =: <fh:set>number2: 3000< /fh:set>  
 *    <fh:set value="myExp: '= (-55/11+$number-($number2*43+12345))'" mergeFields="{number: 2000}" />
 * 
 * If you copy and run the expression above, you'll notice that it returns an incorrect result. Debugging reveals that the expression is  
 * evaluated as (-55/11+2000-(20002*43+12345)) instead of (-55/11+2000-(3000*43+12345)).  
 * This happens because the mergeFields array is processed first, replacing both $number and $number2 incorrectly, since  
 * $number2 is partially matched as $number. !The parser will always do an in-place replacement!
 * To prevent such issues, it is recommended to append another $ at the end of placeholders.
 * 
 * The correct expression should be:
 * 
 * <fh:set>number2: 3000< /fh:set>  
 * <fh:set value="myExp: '= (-55/11+$number$-($number2$*43+12345))'" mergeFields="{number: 2000}" />
 * 
 * @param array mergeFields Define merge fields to replace placeholders in strings or mathematical expressions.  
 * Keys can be numerical or strings. Values can be of any type, but objects must implement a __toString function.  
 * Syntax: {key1: 1, 0: 'value1', key2: 'value2', 1: 43}
 * 
 * @param bool force If false, the view helper will throw an exception if the variable name already exists in the global variable provider.  
 * Set to true to overwrite existing keys.
 */

final class SetViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'The variables are set to the global scope.');
        $this->registerArgument('mergeFields', 'mixed', 'If variable is a string or mathematical expression which contains other variables those can be forwarded here.');
        $this->registerArgument('force', 'boolean', 'If true existing keys will be overwritten. Otherwise an exception will be thrown.', false, false);
    }

    private function _compileExpression($expression, $mergeFields): int|float
    {
        $expression = $this->_injectMergefield($expression, $mergeFields);
        $me = new MathematicalExpressions();
        $value = $me->compileExpression($expression);
        return str_contains($value, '.') ? floatval($value) : intval($value);
    }

    private function _injectMergefield(string $variable, array $mergeFields): string
    {
        $matches = [];
        preg_match_all("/(?<!\/)\\\$[a-zA-Z_][a-zA-Z0-9_]*\\\${0,1}/", $variable, $matches, PREG_PATTERN_ORDER);
        if (!empty($matches) && !empty($matches[0])) {
            $canUseMergefieldsArray = $mergeFields && is_array($mergeFields) && !empty($mergeFields);
            $matches[0] = array_unique($matches[0]);
            foreach ($matches[0] as $match) {
                $len = strlen($match);
                $suffixToken = $match[$len-1] === '$' ? '$' : '';
                $match = trim($match, '$');
                if ($canUseMergefieldsArray && isset($mergeFields[$match])) {
                    $variable = Utility::injectMergeField($variable, '$', $match, $mergeFields[$match], $suffixToken); 
                }
                else if ($this->renderingContext->getVariableProvider()->exists($match)) {
                    $variable = Utility::injectMergeField($variable, '$', $match, $this->renderingContext->getVariableProvider()->get($match), $suffixToken);
                }
                
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

    private function _handleStringValue($value, $mergeFields): mixed
    {
        $value = substr($value, 1, -1);
        if ($value === '') return '';
        $len = strlen($value);
        $lastChar = $value[$len-1];
        switch ($value[0]) {
            case '{':
                if ($lastChar === '}') {
                    return json_decode($value);
                } else {
                    throw new SetViewHelperException (
                        "Error in syntax: Expected closing '}' at the end of variable $value." .
                        "Wrap it with single or double quotes if the variable was supposed to be of type string."
                    );
                }
                break;
            case '[':
                if ($lastChar === ']') {
                    return json_decode($value);
                } else {
                    throw new SetViewHelperException (
                        "Error in syntax: Expected closing ']' at the end of variable $value." .
                        "Wrap it with single or double quotes if the variable was supposed to be of type string."
                    );
                }
                break;
            case '=':
                $value = substr($value, 1, $len);
                return $this->_compileExpression($value, $mergeFields);
                break;
            default:
                if ($value[0] === '\\') $value = substr($value, 1, $len);
                return $this->_injectMergefield($value, $mergeFields);
                break;
        }
    }

    private function _processStringVariable($value, $mergeFields): mixed
    {
        $lastChar = $value[strlen($value)-1];
        switch ($value[0]) {
            case '"':
                if ($lastChar === '"') {
                    $value = $this->_handleStringValue($value, $mergeFields);
                } else {
                    throw new SetViewHelperException (
                        "Error in syntax: Expected closing '\"' at the end of variable $value. " .
                        "Wrap it with single or double quotes if the variable was supposed to be of type string."
                    );
                }
                break;
            case "'":
                if ($lastChar === "'") {
                    $value = $this->_handleStringValue($value, $mergeFields);
                } else {

                    throw new SetViewHelperException (
                        "Error in syntax: Expected closing \"'\" at the end of variable $value. " .
                        "Wrap it with single or double quotes if the variable was supposed to be of type string."
                    );
                }
                break;
            case '{':
                if ($lastChar === '}') {
                    return json_decode($value);
                } else {
                    throw new SetViewHelperException (
                        "Error in syntax: Expected closing '}' at the end of variable $value." .
                        "Wrap it with single or double quotes if the variable was supposed to be of type string."
                    );
                }
                break;
            case '[':
                if ($lastChar === ']') {
                    return json_decode($value);
                } else {
                    throw new SetViewHelperException (
                        "Error in syntax: Expected closing ']' at the end of variable $value." .
                        "Wrap it with single or double quotes if the variable was supposed to be of type string."
                    );
                }
                break;
            case '=':
                $value = substr($value, 1, strlen($value));
                return $this->_compileExpression($value, $mergeFields);
                break;
            default:
                $lowVal = strtolower($value);
                if ($lowVal === 'true') $value = true;
                if ($lowVal === 'false') $value = false;
                if (is_numeric($value)) {
                    $value = str_contains($value, '.') ? floatval($value) : intval($value);
                }
                if (is_string($value)) $value = $this->_injectMergefield($value, $mergeFields);
                break;
        }
        return $value;
    }

    private function _lexVariables($variables, $mergeFields): array {
        $results = [];
        if (is_array($variables)) {
            foreach ($variables as $key => $value) {
                $key = is_string($key) ? trim($key) : $key;
                if (is_string($value)) {
                    $value = trim($value);
                    $results[$key] = $this->_processStringVariable($value, $mergeFields);
                } else {
                    $results[$key] = $value;
                }
            }
        } else if (is_string($variables)) {
            $splittedVars = Utility::stringSafeExplode(',', $variables);
            $variables = [];
            foreach ($splittedVars as $variable) {
                if (str_contains($variable, ':')) {
                    $variable = Utility::stringSafeExplode(':', $variable);
                    if (sizeof($variable) !== 2) {
                        throw new SetViewHelperException(
                            "Syntax error: Variables must contain format key: value. Found too many colons in the expression."
                        );
                    }
                    $key = is_string($variable[0]) ? trim($variable[0]) : $variable[0];
                    $value = trim($variable[1]);
                    $results[$key] = $this->_processStringVariable($value, $mergeFields);
                } else {
                    throw new SetViewHelperException("Syntax error: Variables must contain format key: value.");
                }
            }
        } else {
            throw new SetViewHelperException("Variables must be of type array or string and format key: value.");
        }
        return $results;
    }

    public function render(): void
    {
        $variables = $this->arguments['value'] ?? ($this->renderChildren() ?? NULL);
        $mergeFields = $this->arguments['mergeFields'] ?? [];
        if (!$variables) throw new SetViewHelperException('No variable(s) provided.');
        $variables = $this->_lexVariables($variables, $mergeFields);
        $globalVars = $this->renderingContext->getVariableProvider();
        foreach ($variables as $key => $variable)
        {
            if ($this->_nameValidator($key))
                throw new SetViewHelperException("Variable name '$key' is reserved. Please choose an other identifier.");

            if ($this->arguments['force'] && $globalVars->exists($key)) $globalVars->remove($key);

            $globalVars->add($key, $variable);
        }
    }

    public function getContentArgumentName(): string
    {
        return 'value';
    }
}

?>