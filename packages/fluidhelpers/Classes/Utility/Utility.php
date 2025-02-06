<?php

/**
 * Author: Dennis Schwab - 2025
 * Licence: GPL2.0 or later
 * Version: 0.1.0
 */

declare(strict_types=1);

namespace DS\fluidHelpers\Utility;

/**
 * A class of useful helper functions
 */
final readonly class Utility
{
    public static function keyValueStringToArray(string $expression): array
    {
        $arr = $matches = [];
        preg_match_all('/(\w+):\s*([\'"]?)(.*?)\2(?=\s*,|\s*$)/', $expression, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) $arr[$match[1]] = $match[3];
        return $arr;
    }

    /**
     * Returns the last element of an array without moving the internal pointer
     * 
     * @param array $arr The array to return the last element from.
     * 
     * @return mixed The last element of the given array.
     */
    public static function array_last(array $arr): mixed
    {
        return $arr[sizeof($arr) - 1];
    }

    /**
     * Checks whether a character is escaped or not.
     * 
     * @param string $string The string to check.
     * 
     * @param int $pos The position of the character which could be escaped in $string.
     * 
     * @return bool True if the character is escaped and false vice versa.
     */
    public static function isEscaped(string $string, int $pos): bool
    {
        $count = 0;
        while ($pos-- >= 0 && $string[$pos] === '\\') $count++;
        return $count % 2 !== 0; 
    }

    /**
     * Use this function when a string is an expression containing other strings that should not be split.
     * 
     * This simple indexed for loop is chosen for performance reasons as regex provides less code but mostly
     * much more computation has to be done.
     * 
     * @param string $delimiter The character or string used to separate the input string at.
     * Matching occurrences will be omitted in the output array.
     * 
     * @param string $string The input string to split into chunks.
     * 
     * @param ?int $limit Read the $limit parameter from PHP's explode() function for a detailed explanation.
     * 
     * @return string[] A numerically indexed array containing the split parts of the input string.
     */
    public static function stringSafeExplode(string $delimiter, string $string, ?int $limit = PHP_INT_MAX): array {
        if ($delimiter === '') return [];
        if ($limit > 0) $limit--;

        $_limit = $limit > 0 ? $limit : ($limit < 0 ? PHP_INT_MAX : 0);
        $result = [];
        $len = strlen($string);
        $delimiterLen = strlen($delimiter) - 1;
        $lastCuttingPosition = 0;
        $insideQuotes = '';
        $firstDelimiterChar = $delimiter[0];
        $isMatch = true;
        $i=0;

        for (; $i < $len && $_limit; $i++) { 
            $char = $string[$i];
            switch ($char) {
                case '"':
                    if (!Utility::isEscaped($string, $i) && $insideQuotes !== "'")
                        $insideQuotes = $insideQuotes === '"' ? '' : '"';
                    break;
                case "'":
                    if (!Utility::isEscaped($string, $i) && $insideQuotes !== '"')
                        $insideQuotes = $insideQuotes === "'" ? '' : "'";
                    break;
                default:
                    if (!$insideQuotes) {
                        if ($char === $firstDelimiterChar) {
                            $isMatch = true;
                            for ($j=$i+1, $k = 1; $k < $delimiterLen; $j++, $k++) { 
                                if ($j >= $len || $string[$j] !== $delimiter[$k]) {
                                    $isMatch = false;
                                    break;
                                }
                            }
                            if ($isMatch) {
                                $result[] = substr($string, $lastCuttingPosition, ($i - $lastCuttingPosition));
                                $i += $delimiterLen + 1;
                                $lastCuttingPosition = $i;
                                $_limit--;
                            }
                        }
                    }
                    break;
            }
        }
        $result[] = substr($string, $lastCuttingPosition, $len);
        if ($limit < 0) $result = array_slice($result, 0, (sizeof($result) + $limit));
        return $result;
    }

    public static function injectMergeField
    (
        string $variable,
        string $prefixToken,
        string $mergeFieldIdentifier,
        mixed $value,
        ?string $suffixToken = ''
    ): mixed
    {
        $pos = 0;
        $prefixLen = strlen($prefixToken);
        $suffixLen = strlen($suffixToken);
        /** $variable is just a placeholder, so we can return the corresponding type directly. */
        if (($prefixToken . $mergeFieldIdentifier . $suffixToken) === $variable) {
            if (is_string($value)) {
                $len = strlen($value);
                $lowVal = strtolower($value);
                if ($lowVal === 'true') return true;
                if ($lowVal === 'false') return false;
                if (is_numeric($value)) {
                    return str_contains($value, '.') ? floatval($value) : intval($value);
                }
                if ($value[0] === '[' && $value[$len-1] === ']') return json_decode($value);
                if ($value[0] === '{' && $value[$len-1] === '}') return json_decode($value);
                return $value;
            }
            else return $value;
        } 

        if (!is_string($value)) {
            if (is_bool($value)) $value = $value === true ? 'true' : 'false';
            if (is_numeric($value)) $value = strval($value);
            if (is_array($value)) $value = json_encode($value);
            if (is_object($value)) $value = method_exists($value, '__toString') ? $value->__toString() : 'object';
        }

        $identifierLen = strlen($mergeFieldIdentifier);
        while (($pos = strpos($variable, $prefixToken, $pos)) !== false) {
            if ((strpos($variable, $mergeFieldIdentifier, ($pos+$prefixLen)) === ($pos+$prefixLen)) && !Utility::isEscaped($variable, $pos)) {
                if ($suffixToken === '' || strpos($variable, $suffixToken, ($pos+$prefixLen+$identifierLen)) === ($pos+$prefixLen+$identifierLen)) {
                    $variable = substr($variable, 0, $pos) . $value . substr($variable, ($pos+$prefixLen+$identifierLen+$suffixLen));
                }
            }
            $pos++;
        }
        return $variable;
    }
    
}

?>