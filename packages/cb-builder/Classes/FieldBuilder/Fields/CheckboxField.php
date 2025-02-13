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

    private function _validateCols($entry): void
    {
        if (
            'inline' === $entry
            || (($entry = filter_var($entry, FILTER_SANITIZE_NUMBER_INT)) !== false && $entry > 0 && $entry < 32)
            || $GLOBALS['CbBuilder']['config']['Strict'] === false
        ) {
            $this->cols = $entry === 'inline' ? 'inline' : intval($entry);
        }
        else {
            throw new Exception("'Checkbox' field configuration 'cols' must contain either 'inline' or an integer " .
            "between 1-31, if set.");
        }
    }

    private function _validateDefault($entry, $config): void
    {
        if (($entry = filter_var($entry, FILTER_SANITIZE_NUMBER_INT)) !== false || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
            $entry = intval($entry);
            if ($entry <= 0xFFFFFFFF || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                if ($entry === 0 || $entry === 1) {
                    $this->default = $entry;
                } else if (isset($config['items']) || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                    if (empty($this->items)) $this->_validateItems($config['items']);
                    if ($this->_validateBITMASK($entry) || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                        $this->default = $entry;
                    } else {
                        throw new Exception (
                            "'Checkbox' field configuration 'default' does not match with the " .
                            "number of checkboxes defined in 'items', if set."
                        );
                    }
                } else {
                    throw new Exception (
                        "'Checkbox' field configuration 'default' does not match with the " .
                        "number of checkboxes defined in 'items'."
                    );
                }
            } else {
                throw new Exception("'Checkbox' field configuration 'default' must be an integer between 0 and 4294967295, if set.");
            }
            
        } else {
            throw new Exception("'Checkbox' field configuration 'default' must be an integer, if set.");
        }
    }

    private function _validateDefaultList($entry, $config): void
    {
        if (is_string($entry) && $entry !== '') {
            $entry = array_map('trim', explode(',', $entry)); 
        }
        if (isset($config['items']) && empty($this->items)) $this->_validateItems($config['items']);
        $this->defaultList = [];
        foreach ($entry as $num) {
            if (($num = filter_var($num, FILTER_SANITIZE_NUMBER_INT)) !== false || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                $this->defaultList[] = intval($num);
            } else {
                throw new Exception (
                    "'Checkbox' field configuration 'defaultList[0-n]' must be an integer, if set."
                );
            }
        }
        $this->_validateDefault($this->_calculateBITMASK(), $config);
    }

    private function _validateEval($entry, $config): void
    {
        if ('maximumRecordsChecked' === $entry || 'maximumRecordsCheckedInPid' === $entry || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
            if (!isset($config['validation'])) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    throw new Exception (
                        "'Checkbox' field configuration 'eval' must be combined with 'validation', if set." .
                        " E.g.: 'eval' => 'maximumRecordsCheckedInPid',\n'validation' => [\n\t'maximumRecordsCheckedInPid' => 1\n]"
                    );
                }
            }
            $this->eval = $entry;
            if (empty($this->validation)) $this->_validateValidation($config['validation'], $config);
            if ($entry !== array_key_first($this->validation[0])) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    throw new Exception (
                        "'Checkbox' field configuration 'eval' must match with 'validation', if set." .
                        " E.g.: 'eval' => 'maximumRecordsCheckedInPid',\n'validation' => [\n\t'maximumRecordsCheckedInPid' => 1\n]"
                    );
                }
            }
        } else {
            throw new Exception (
                "'Checkbox' field configuration 'eval' must either contain 'maximumRecordsChecked' or " .
                "'maximumRecordsCheckedInPid', if set."
            );
        }
    }

    private function _validateItems($entry): void
    {

        if (is_array($entry)) {
            $this->items = [];
            foreach ($entry as $item) {
                if (is_array($item) || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                    foreach ($item as $key => $property) {
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            switch ($key) {
                                case 'label':
                                    if (!is_string($property)) {
                                        throw new Exception (
                                            "'Checkbox' field configuration 'items[0-n]['label' => 'value']' must " .
                                            "have a string label identifier, if set."
                                        );
                                    }
                                    break;
                                case 'invertStateDisplay':
                                    if (!is_bool($property)) {
                                        throw new Exception (
                                            "'Checkbox' field configuration 'invertStateDisplay' must " .
                                            "be of type boolean, if set."
                                        );
                                    }
                                    break;
                                case 'iconIdentifierChecked':
                                    if (!is_string($property)) {
                                        throw new Exception (
                                            "'Checkbox' field configuration 'iconIdentifierChecked' must " .
                                            "have a string icon identifier, if set."
                                        );
                                    }
                                    break;
                                case 'iconIdentifierUnchecked':
                                    if (!is_string($property)) {
                                        throw new Exception (
                                            "'Checkbox' field configuration 'iconIdentifierUnchecked' must " .
                                            "have a string icon identifier, if set."
                                        );
                                    }
                                    break;
                                case 'labelChecked':
                                    if (!is_string($property)) {
                                        throw new Exception (
                                            "'Checkbox' field configuration 'labelChecked' must " .
                                            "be of type string, if set."
                                        );
                                    }
                                    break;
                                case 'labelUnchecked':
                                    if (!is_string($property)) {
                                        throw new Exception (
                                            "'Checkbox' field configuration 'labelUnchecked' must " .
                                            "be of type string, if set."
                                        );
                                    }
                                    break;
                                default:
                                    // skip
                                    break;
                            }
                        }
                    }
                    $this->items[] = $item;
                } else {
                    throw new Exception (
                        "'Number' field configuration 'items[0-n]' each item must be of type array."
                    );
                }
            }
        }
    }

    private function _validateItemsProcFunc($entry): void
    {
        if ((str_contains($entry, '->') && str_contains($entry, '::class')) || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
            $this->itemsProcFunc = $entry;
        } else {
            throw new Exception (
                "'Checkbox' field configuration 'itemsProcFunc' must contain a class path and name and method name in format: " .
                "'\VENDOR\Extension\UserFunction\FormEngine\YourClass::class->yourMethod', if set."
            );
        }
    }

    private function _validateRenderType($entry): void
    {
        if (
            $entry === 'check'
            || $entry === 'checkboxToggle'
            || $entry === 'checkboxLabeledToggle'
            || $GLOBALS['CbBuilder']['config']['Strict'] === true
        ) {
            $this->renderType = $entry;
        } else {
            throw new Exception (
                "'Checkbox' field configuration 'renderType' must contain either the keyword 'check', 'checkboxToggle' " .
                "or 'checkboxLabeledToggle', if set."
            );
        }
    }

    private function _validateValidation($entry, $config): void
    {
        if (is_array($entry) || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
            if (count($entry) === 1 || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                $key = array_key_first($entry[0]);
                if ('maximumRecordsChecked' === $key || 'maximumRecordsCheckedInPid' === $key || $GLOBALS['CbBuilder']['config']['Strict'] === false) {
                    if (!isset($config['eval'])) {
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            throw new Exception (
                                "'Checkbox' field configuration 'validation' must be combined with 'eval', if set." .
                                " E.g.: 'eval' => 'maximumRecordsCheckedInPid',\n'validation' => [\n\t'maximumRecordsCheckedInPid' => 1\n]"
                            );
                        }
                    }
                    $this->validation = $entry;
                    if ($this->eval === '') $this->_validateEval($config['eval'], $config);
                    if ($key !== $this->eval) {
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            throw new Exception (
                                "'Checkbox' field configuration 'validation' must match with 'eval', if set." .
                                " E.g.: 'eval' => 'maximumRecordsCheckedInPid',\n'validation' => [\n\t'maximumRecordsCheckedInPid' => 1\n]"
                            );
                        }
                    } 
                } else {
                    throw new Exception (
                        "'Checkbox' field configuration 'validation' must either contain 'maximumRecordsChecked' or " .
                        "'maximumRecordsCheckedInPid', if set."
                    );
                }    
            } else {
                throw new Exception (
                    "'Checkbox' field configuration 'validation' must contain exactly one element of type array, if set."
                );
            }    
        } else {
            throw new Exception (
                "'Checkbox' field configuration 'validation' must be of type array, if set."
            );
        }    
    }

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'cols':
                        $this->_validateCols($value);
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
                        if (empty($this->items)) $this->_validateItems($value);
                        break;
                    case 'itemsProcFunc':
                        $this->_validateItemsProcFunc($value);
                        break;
                    case 'renderType':
                        $this->_validateRenderType($value);
                        break;
                    case 'validation':
                        if (empty($this->validation)) $this->_validateValidation($value, $config);
                        break;
                    default:
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
        $config = new CheckboxFieldConfig();
        $config->arrayToConfig($field);
        $this->config = $config;
        $this->__arrayToField('checkbox', $field);
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