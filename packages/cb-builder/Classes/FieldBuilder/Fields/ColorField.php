<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;

final class ColorFieldConfig extends Config
{
    protected string $default = '';
    protected string $mode = '';
    protected bool $nullable = false;
    protected bool $opacity = false;
    protected string $placeholder = '';
    protected bool $required = false;
    protected bool $readOnly = false;
    protected int $size = 30;
    protected array $valuePicker = [];

    private function _validateMode($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Color' field '$identifier' configuration 'mode' must be of type string, if set."
            );
        }
        if ('useOrOverridePlaceholder' !== $entry) {
            throw new Exception (
                "'Color' field '$identifier' configuration 'mode' must contain the keyword 'useOrOverridePlaceholder', if set."
            );
        }
        if (!isset($config['placeholder'])) {
            throw new Exception (
                "'Color' field '$identifier' configuration 'mode' must takes only effect if a placeholder is defined as well, if set."
            );
        }
    }

    private function _validateColorHex (
        $entry, array $config, ?string $type = '', ?string $notStringException = '',
        ?string $wrongFormatException = '', ?string $opacityDisabled = ''
    ): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            if ($type !== '') {
                throw new Exception (
                    "'Color' field '$identifier' configuration '$type' must be of type string, if set."
                );
            } else {
                throw new Exception ($notStringException);
            }
            
        }

        $match = [];

        preg_match("/#([A-Fa-f0-9]{8}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{4}|[A-Fa-f0-9]{3})/", $entry, $match);
        if ($match[0] !== $entry) {
            if ($type !== '') {
                throw new Exception (
                    "'Color' field '$identifier' configuration '$type' must be a valid html color hex " .
                    "code in format RRGGBB, RRGGBBAA, RGB or RGBA, if set."
                );
            } else {
                throw new Exception ($wrongFormatException);
            }
        }

        if ((strlen($entry) === 5 || strlen($entry) === 9) && (!isset($config['opacity']) || !$config['opacity'])) {
            if ($type !== '') {
                throw new Exception (
                    "'Color' field '$identifier' configuration '$type' has format RGBA or RRGGBBAA but opacity is not set or " .
                    "set to false. Omit the opacity values or set ['config']['opacity'] to true."
                );
            } else {
                throw new Exception ($opacityDisabled);
            }
        }
    }

    private function _validateSize($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_int($entry)) {
            throw new Exception (
                "'Color' field '$identifier' configuration 'size' must be of type integer, if set."
            );
        }
        $entry = intval($entry);
        if ($entry < 10 || $entry > 50) {
            throw new Exception (
                "'Color' field '$identifier' configuration 'size' must be a range between 10 and 50, if set."
            );
        }
    }

    private function _validateValuePicker($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Color' field '$identifier' configuration 'valuePicker' must be of type array, if set."
            );
        }
        
        if (!isset($entry['items'])) {
            throw new Exception (
                "'Color' field '$identifier' configuration 'valuePicker' must contain an array with the key 'items', if set."
            );
        }
        
        if (!is_array($entry['items'])) {
            throw new Exception (
                "'Color' field '$identifier' configuration 'valuePicker['items']' must be of type array, if set."
            );
        }
        
        $this->valuePicker['items'] = [];
        $i = 0;
        foreach ($entry['items'] as $item) {
            if (!is_array($item)) {
                throw new Exception (
                    "'Color' field '$identifier' configuration 'valuePicker['items'][$i]' must be of type array. " .
                    "Fix:\nvaluePicker:\n  items:\n    - key1: #ABC\n    - key2: #AA11BB\n    - key3: AA11BB22"
                );
            }
            if (count($item) !== 1) {
                throw new Exception (
                    "'Color' field '$identifier' configuration 'valuePicker['items'][$i]' must contain exactly one key => value entry." .
                    "Fix:\nvaluePicker:\n  items:\n    - key1: #ABC\n    - key2: #AA11BB\n    - key3: AA11BB22"
                );
            }
            $key = array_key_first($item);
            if (!is_string($key)) {
                throw new Exception (
                    "'Color' field '$identifier' configuration 'valuePicker['items'][$i][0]' key must be of type string." .
                    "Fix:\nvaluePicker:\n  items:\n    - key1: #ABC\n    - key2: #AA11BB\n    - key3: AA11BB22"
                );
            }
            $value = $item[$key];
            $this->_validateColorHex (
                $value, $config, '',
                "'Color' field '$identifier' configuration 'valuePicker['items'][$i][0]' value must be of type string.",
                "'Color' field '$identifier' configuration 'valuePicker['items'][$i][0]' value must be a valid html color hex " .
                "code in format RRGGBB, RRGGBBAA, RGB or RGBA, if set.",
                "'Color' field '$identifier' configuration 'valuePicker['items'][$i][0]' has format RGBA or RRGGBBAA but " .
                "opacity is not set or set to false. Omit the opacity values or set ['config']['opacity'] to true."
            );
            $this->valuePicker['items'][] = $item;
            $i++;
        }
    }

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    switch ($configKey) {
                        case 'default':
                            $this->_validateColorHex($value, $config, 'default');
                            $this->default = $value;
                            break;
                        case 'mode':
                            $this->_validateMode($value, $config);
                            $this->mode = $value;
                            break;
                        case 'placeholder':
                            $this->_validateColorHex($value, $config, 'placeholder');
                            $this->placeholder = $value;
                            break;
                        case 'size':
                            $this->_validateSize($value, $config);
                            $this->size = $value;
                            break;
                        case 'valuePicker':
                            $this->_validateValuePicker($value, $config);
                            $this->valuePicker = $value;
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
        return parent::_configToElement('color', $properties);
    }
}

final class ColorField extends Fields
{
    protected ColorFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('color', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new ColorFieldConfig();
        $config->arrayToConfig($field);
        $this->config = $config;
        
    }

    public function fieldToElement(): array
    {
        $element = [];
        $element['config'] = $this->config->configToElement();
        return $element;
    }

    public function __construct(array $field, string $table)
    {
        $this->table = $table;
        $this->_arrayToField($field);
    }
}