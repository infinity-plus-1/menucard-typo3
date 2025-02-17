<?php

/**
 * Author: Dennis Schwab - 2025
 * Licence: GPL2.0 or later
 * Version: 0.1.0
 */

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use Exception;

final class CheckboxFieldConfig extends Config
{
    protected int|string $cols = '';
    protected int $default = -1;
    protected string|array $defaultList = '';
    protected string $eval = '';
    protected bool $invertStateDisplay = false;
    protected array $items = [];
    protected string $itemsProcFunc = '';
    protected bool $readOnly = false;
    protected string $renderType = 'check';
    protected array $validation = [];

    const RENDER_TYPES = [
        'check', 'checkboxToggle', 'checkboxLabeledToggle'
    ];

    const EVAL_KEYWORDS = [
        'maximumRecordsChecked', 'maximumRecordsCheckedInPid'
    ];

    private function _calculateBITMASK(): int
    {
        $set = 0;
        $list = $this->defaultList;
        foreach ($list as $enabledPos) {
            if ($enabledPos > 0 && $enabledPos < 33) {
                $set |= (1 << ($enabledPos - 1));
            }
        }
        return $set;
    }

    private function _validateBITMASK($bitmask): bool
    {
        if (empty($this->items) && $bitmask > 1) return false;
        $count = count($this->items);
        if ($count === 32 && $bitmask === 2147483647) return false;
        /** ~(0 - (1 << count($this->items))) >= bitmask */
        return ((~(abs(~(PHP_INT_MAX - (1 << $count))))) & (0x7FFFFFFFFFFFFFFF)) >= $bitmask ;
    }

    private function _validateCols($entry, $config): void
    {
        $identifier = $config['identifier'];
        if ('inline' === $entry) {
            $this->cols = 'inline';
        } else if ($entry = $this->handleIntegers($entry)) {
            if ($entry <= 0 || $entry >= 32) {
                $this->cols = $entry;
            }
        } else {
            throw new Exception("'Checkbox' field '$identifier' configuration 'cols' must contain either 'inline' or an integer " .
            "between 1-31, if set.");
        }
    }

    private function _validateDefault($entry, $config): void
    {
        $identifier = $config['identifier'];
        if ($entry = $this->handleIntegers($entry)) {
            if ($entry > 0xFFFFFFFF) {
                throw new Exception("'Checkbox' field '$identifier' configuration 'default' must be an integer between 0 and 4294967295, if set.");
            }
            if ($entry === 0 || $entry === 1) {
                $this->default = $entry;
            } else if (isset($config['items'])) {
                if (empty($this->items)) $this->_validateItems($config['items'], $config);
                if ($this->_validateBITMASK($entry)) {
                    $this->default = $entry;
                } else {
                    throw new Exception (
                        "'Checkbox' field '$identifier' configuration 'default' does not match with the " .
                        "number of checkboxes defined in 'items', if set."
                    );
                }
            } else {
                throw new Exception (
                    "'Checkbox' field '$identifier' configuration 'default' does not match with the " .
                    "number of checkboxes defined in 'items'."
                );
            }
        } else {
            throw new Exception("'Checkbox' field '$identifier' configuration 'default' must be an integer, if set.");
        }
    }

    private function _validateDefaultList($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (is_string($entry) && $entry !== '') {
            $entry = array_map('trim', explode(',', $entry)); 
        }
        if (isset($config['items']) && empty($this->items)) $this->_validateItems($config['items'], $config);
        $this->defaultList = [];
        $i = 0;
        foreach ($entry as $num) {
            if ($num = $this->handleIntegers($num)) {
                $this->defaultList[] = $num;
            } else {
                throw new Exception (
                    "'Checkbox' field '$identifier' configuration 'defaultList[$i]' must be an integer, if set."
                );
            }
            $i++;
        }
        $this->_validateDefault($this->_calculateBITMASK(), $config);
    }

    private function _validateEval($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!in_array($entry, self::EVAL_KEYWORDS)) {
            throw new Exception (
                "'Checkbox' field '$identifier' configuration 'eval' must contain one of the following keywords, if set: " .
                implode(', ', self::EVAL_KEYWORDS)
            );
        }
        if (!isset($config['validation'])) {
            throw new Exception (
                "'Checkbox' field '$identifier' configuration 'eval' must be combined with 'validation', if set." .
                " Fix: 'eval' => '$entry',\n'validation' => [\n\t'$entry' => 1\n]"
            );
        }
        $this->eval = $entry;
        if (empty($this->validation)) $this->_validateValidation($config['validation'], $config);
        if ($entry !== array_key_first($this->validation[0])) {
            throw new Exception (
                "'Checkbox' field '$identifier' configuration 'eval' must match with 'validation', if set." .
                " E.g.: 'eval' => '$entry',\n'validation' => [\n\t'$entry' => 1\n]"
            );
        }
    }

    private function _validateItems($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Checkbox' field '$identifier' configuration 'items' must be of type array."
            );
        }
        $this->items = [];
        $i = 0;
        foreach ($entry as $item) {
            if (!is_array($item)) {
                throw new Exception (
                    "'Number' field '$identifier' configuration 'items[$i]' each item must be of type array."
                );
            }
            foreach ($item as $key => $property) {
                switch ($key) {
                    case 'label':
                        if (!is_string($property)) {
                            throw new Exception (
                                "'Checkbox' field '$identifier' configuration 'items[$i]['label' => 'value']' must " .
                                "have a string label identifier, if set."
                            );
                        }
                        break;
                    case 'invertStateDisplay':
                        if (!is_bool($property)) {
                            throw new Exception (
                                "'Checkbox' field '$identifier' configuration 'items[$i][$key]' must " .
                                "be of type boolean, if set."
                            );
                        }
                        break;
                    case 'iconIdentifierChecked':
                        if (!is_string($property)) {
                            throw new Exception (
                                "'Checkbox' field '$identifier' configuration 'items[$i][$key]' must " .
                                "have a string icon identifier, if set."
                            );
                        }
                        break;
                    case 'iconIdentifierUnchecked':
                        if (!is_string($property)) {
                            throw new Exception (
                                "'Checkbox' field '$identifier' configuration 'items[$i][$key]' must " .
                                "have a string icon identifier, if set."
                            );
                        }
                        break;
                    case 'labelChecked':
                        if (!is_string($property)) {
                            throw new Exception (
                                "'Checkbox' field '$identifier' configuration 'items[$i][$key]' must " .
                                "be of type string, if set."
                            );
                        }
                        break;
                    case 'labelUnchecked':
                        if (!is_string($property)) {
                            throw new Exception (
                                "'Checkbox' field '$identifier' configuration 'items[$i][$key]' must " .
                                "be of type string, if set."
                            );
                        }
                        break;
                    default:
                        throw new Exception (
                            "'Checkbox' field '$identifier' configuration 'items[$i][$key]' is not a valid key, if set. " .
                            "Fix: Remove $key entry from items[$i]"
                        );
                        break;
                }
                $this->items[] = $item;
                $i++;
            }
        }
    }

    private function _validateItemsProcFunc($entry, $config): void
    {
        $identifier = $config['identifier'];
        if ((str_contains($entry, '->') && str_contains($entry, '::class'))) {
            $this->itemsProcFunc = $entry;
        } else {
            throw new Exception (
                "'Checkbox' field '$identifier' configuration 'itemsProcFunc' must contain a class path and name and method name in format: " .
                "'\VENDOR\Extension\UserFunction\FormEngine\YourClass::class->yourMethod', if set."
            );
        }
    }

    private function _validateRenderType($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!in_array($entry, self::RENDER_TYPES)) {
            $this->renderType = $entry;
        } else {
            throw new Exception (
                "'Checkbox' field '$identifier' configuration 'renderType' must contain either the keyword 'check', 'checkboxToggle' " .
                "or 'checkboxLabeledToggle', if set."
            );
        }
    }

    private function _validateValidation($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (is_array($entry) || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
            if (count($entry) === 1 || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                if (!isset($entry[0])) {
                    throw new Exception (
                        "'Checkbox' field '$identifier' configuration 'validation[]' must contain exactly one element at index 0, if set." .
                        " Fix:\nvalidation:\n  - maximumRecordsChecked: 1"
                    );
                }
                $key = array_key_first($entry[0]);
                if ('maximumRecordsChecked' === $key || 'maximumRecordsCheckedInPid' === $key || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                    if (!isset($config['eval'])) {
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            throw new Exception (
                                "'Checkbox' field '$identifier' configuration 'validation' must be combined with 'eval', if set." .
                                " E.g.: 'eval' => 'maximumRecordsCheckedInPid',\n'validation' => [\n\t'maximumRecordsCheckedInPid' => 1\n]"
                            );
                        }
                    }
                    if (!is_int($entry[0][$key])) {
                        throw new Exception (
                            "'Checkbox' field '$identifier' configuration 'validation[0][key]' must contain an integer value, if set."
                        );
                    }
                    $this->validation = $entry;
                    if ($this->eval === '') $this->_validateEval($config['eval'], $config);
                    if ($key !== $this->eval) {
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            throw new Exception (
                                "'Checkbox' field '$identifier' configuration 'validation' must match with 'eval', if set." .
                                " E.g.: 'eval' => 'maximumRecordsCheckedInPid',\n'validation' => [\n\t'maximumRecordsCheckedInPid' => 1\n]"
                            );
                        }
                    } 
                } else {
                    throw new Exception (
                        "'Checkbox' field '$identifier' configuration 'validation[0]' must either contain 'maximumRecordsChecked' or " .
                        "'maximumRecordsCheckedInPid', if set."
                    );
                }    
            } else {
                throw new Exception (
                    "'Checkbox' field '$identifier' configuration 'validation' must contain exactly one element of type array, if set."
                );
            }    
        } else {
            throw new Exception (
                "'Checkbox' field '$identifier' configuration 'validation' must be of type array, if set."
            );
        }    
    }

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    switch ($configKey) {
                        case 'cols':
                            $this->_validateCols($value, $config);
                            break;
                        case 'default':
                            if ($this->default < 0) $this->_validateDefault($value, $config);
                            break;
                        case 'defaultList':
                            if (empty($this->defaultList)) $this->_validateDefaultList($value, $config);
                            break;
                        case 'eval':
                            if ($this->eval === '') $this->_validateEval($value, $config);
                            break;
                        case 'items':
                            if (empty($this->items)) $this->_validateItems($value, $config);
                            break;
                        case 'itemsProcFunc':
                            $this->_validateItemsProcFunc($value, $config);
                            break;
                        case 'renderType':
                            $this->_validateRenderType($value, $config);
                            break;
                        case 'validation':
                            if (empty($this->validation)) $this->_validateValidation($value, $config);
                            break;
                        default:
                            $this->$configKey = $value;
                    }
                } else {
                    $this->$configKey = $value;
                }
            }
        }
    }

    public function configToElement(): array
    {
        $properties = get_object_vars($this);
        return parent::_configToElement('checkbox', $properties);
    }
}

/**
 * Parse a checkbox field from fields.yaml
 */
final class CheckboxField extends Fields
{
    protected CheckboxFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('checkbox', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new CheckboxFieldConfig();
        $config->arrayToConfig($field);
        $this->config = $config;
    }

    public function fieldToElement(): array
    {
        $element = [];
        $element['config'] = $this->config->configToElement();
        return $element;
    }

    public function __construct(array $field)
    {
        $this->_arrayToField($field);
    }
}