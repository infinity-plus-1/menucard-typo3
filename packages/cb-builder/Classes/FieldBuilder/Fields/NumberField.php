<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\Utility;
use Exception;

final class NumberFieldConfig extends Config
{
    protected bool $autocomplete = false;
    protected string $default = '';
    protected string $format = '';
    protected string $mode = '';
    protected bool $nullable = false;
    protected array $range = [];
    protected bool $readOnly = false;
    protected bool $required = false;
    protected int $size = -1;
    protected array $slider = [];
    protected array $valuePicker = [];

    private function _validateFormat($entry, $config): void
    {
        $identifier = $config['identifier'];
        if ('integer' !== $entry && 'decimal' !== $entry) {
            throw new Exception("'Number' field '$identifier' configuration 'format' must contain either 'integer' or 'decimal', if set.");
        }
    }

    private function _validateMode($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Number' field '$identifier' configuration 'mode' must be of type string, if set."
            );
        }
        if ('useOrOverridePlaceholder' !== $entry) {
            throw new Exception (
                "'Number' field '$identifier' configuration 'mode' must contain the keyword 'useOrOverridePlaceholder', if set."
            );
        }
        if (!isset($config['placeholder'])) {
            throw new Exception (
                "'Number' field '$identifier' configuration 'mode' must takes only effect if a placeholder is defined as well."
            );
        }
    }

    private function _validateAndSetRange($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (isset($entry['lower'])) {
            if (($numLow = $this->handleIntegers($entry['lower'])) === false) {
                throw new Exception (
                    "'Number' field '$identifier' configuration 'range['lower']' must contain an integer, if set."
                );
                
            }
        }
        if (isset($entry['upper'])) {
            if (($numUp = $this->handleIntegers($entry['upper'])) === false) {
                throw new Exception (
                    "'Number' field '$identifier' configuration 'range['upper']' must contain an integer, if set."
                );
            }
        }

        if ($numUp < $numLow) {
            throw new Exception (
                "'Number' field '$identifier' configuration 'range' entry 'upper' is smaller than 'lower'. Fix: " .
                "Swap values."
            );
        }
        $this->range['upper'] = $numUp;
        $this->range['lower'] = $numLow;
    }

    private function _validateAndSetSlider($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (isset($entry['step'])) {
            if (($num = Utility::toNumber($entry['step'])) !== false) {
                $this->slider['step'] = $num;
            } else {
                throw new Exception (
                    "'Number' field '$identifier' configuration 'slider['step']' must contain an integer or float, if set."
                );
            }
        }
        if (isset($entry['width']) && $num = $this->handleIntegers($entry['width'])) $this->slider['width'] = $num;
    }

    private function _validateAndSetValuePicker($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (array_key_exists('mode', $entry)) {
            if ('append' === $entry['mode'] || 'prepend' === $entry['mode'] || '' === $entry['mode']) {
                $this->valuePicker['mode'] = $entry['mode'];
            } else {
                throw new Exception (
                    "'Number' field '$identifier' configuration 'valuePicker['mode']' must either contain the keyword 'append' or " .
                    "prepend, if set."
                );
            }
        }
        if (isset($entry['items'])) {
            if (!is_array($entry['items'])) {
                throw new Exception (
                    "'Number' field '$identifier' configuration 'valuePicker['items']' must be of type array."
                );
            }
            $this->valuePicker['items'] = [];
            $i = 0;
            foreach ($entry['items'] as $item) {
                if (!is_array($item)) {
                    throw new Exception (
                        "'Number' field '$identifier' configuration 'valuePicker['items'][$i]' must be of type array."
                    );
                }
                if (count($item) !== 1) {
                    throw new Exception (
                        "'Number' field '$identifier' configuration 'valuePicker['items'][$i]' must be an array with exactly one entry."
                    );
                }
                if (!is_string(array_key_first($item))) {
                    throw new Exception (
                        "'Number' field '$identifier' configuration 'valuePicker['items'][$i][key => value]' must " .
                        "have a string key, if set."
                    );
                }
                
                if (($num = Utility::toNumber(current($item))) !== false) {
                    $this->valuePicker['items'][] = [array_key_first($item) => $num];
                } else {
                    if ($num = $this->handleIntegers(current($item))) {
                        $this->valuePicker['items'][] = [array_key_first($item) => $num];
                    } else {
                        throw new Exception (
                            "'Number' field '$identifier' configuration 'valuePicker['items'][$i][key => value]' value must " .
                            "be a string representing a numeric value, an integer or a float, if set."
                        );
                    }
                }
                $i++;    
            }
        }
    }

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    switch ($configKey) {
                        case 'format':
                            $this->_validateFormat($value, $config);
                            $this->format = $value;
                            break;
                        case 'mode':
                            $this->_validateMode($value, $config);
                            $this->mode = $value;
                            break;
                        case 'range':
                            $this->_validateAndSetRange($value, $config);
                            break;
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'Number', 10, 50);
                            $this->size = intval($value);
                            break;
                        case 'slider':
                            $this->_validateAndSetSlider($value, $config);
                            break;
                        case 'valuePicker':
                            $this->_validateAndSetValuePicker($value, $config);
                            break;
                        default:
                            $this->$configKey = $value;
                            break;
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
        return parent::_configToElement('number', $properties);
    }
}

final class NumberField extends Fields
{
    protected NumberFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('number', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new NumberFieldConfig();
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