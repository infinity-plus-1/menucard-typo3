<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

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

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'format':
                        if ('integer' === $value || 'decimal' === $value) $this->format = $value;
                        else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            throw new Exception("'Number' field configuration 'format' must contain either 'integer' or 'decimal', if set.");
                        }
                        break;
                    case 'mode':
                        if ('useOrOverridePlaceholder' === $value) $this->mode = $value;
                        else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            throw new Exception("'Number' field configuration 'mode' must contain 'useOrOverridePlaceholder', if set.");
                        }
                        break;
                    case 'range':
                        if (isset($value['lower'])) {
                            if ($numLow = filter_var($value['lower'], FILTER_SANITIZE_NUMBER_INT)) {
                                $this->range['lower'] = intval($numLow);
                            } else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                                throw new Exception (
                                    "'Number' field configuration 'range['lower']' must contain an integer, if set."
                                );
                            }
                        }
                        if (isset($value['upper'])) {
                            if ($numUp = filter_var($value['upper'], FILTER_SANITIZE_NUMBER_INT)) {
                                $this->range['upper'] = intval($numUp);
                            } else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                                throw new Exception (
                                    "'Number' field configuration 'range['upper']' must contain an integer, if set."
                                );
                            }
                        }
                        break;
                    case 'size':
                        if ($value = filter_var($value, FILTER_SANITIZE_NUMBER_INT)) {
                            $value = intval($value);
                            if ($value >= 10 && $value <= 50) {
                                $this->size = intval($value);
                            } else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                                throw new Exception (
                                    "'Number' field configuration 'size' must be in range between 10 and 50, if set."
                                );
                            }
                            
                        } else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            throw new Exception (
                                "'Number' field configuration 'size' must contain an integer, if set."
                            );
                        }
                        break;
                    case 'slider':
                        if (isset($value['step'])) {
                            if (is_numeric($value['step'])) {
                                $this->slider['step'] = $value['step'];
                            } else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                                throw new Exception (
                                    "'Number' field configuration 'slider['step']' must contain an integer or float, if set."
                                );
                            }
                        }
                        if (isset($value['width'])) $this->slider['width'] = $value['width'];
                        break;
                    case 'valuePicker':
                        if (array_key_exists('mode', $value)) {
                            if ('append' === $value['mode'] || 'prepend' === $value['mode'] || '' === $value['mode']) {
                                $this->valuePicker['mode'] = $value['mode'];
                            } else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                                throw new Exception (
                                    "'Number' field configuration 'valuePicker['mode']' must either contain the keyword 'append' or " .
                                    "prepend, if set."
                                );
                            }
                        }
                        if (isset($value['items']) && is_array($value['items'])) {
                            $this->valuePicker['items'] = [];
                            foreach ($value['items'] as $item) {
                                if (is_array($item) && count($item) === 1) {
                                    if (is_string(array_key_first($item))) {
                                        $this->valuePicker['items'][] = $item;
                                    } else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                                        throw new Exception (
                                            "'Number' field configuration 'valuePicker['items'][0-n][key => value]' must " .
                                            "have a string key, if set."
                                        );
                                    }
                                } else if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                                    throw new Exception (
                                        "'Number' field configuration 'valuePicker['items'][0-n]' must be an array with exactly one entry."
                                    );
                                }
                            }
                        }
                        break;
                }
                $this->$configKey = $value;
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
        $config = new NumberFieldConfig();
        $config->arrayToConfig($field);
        $this->config = $config;
        $this->__arrayToField('number', $field);
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