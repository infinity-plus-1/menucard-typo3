<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;

final class RadioFieldConfig extends Config
{
    protected string $default = '';
    protected array $items = [];
    protected string $itemsProcFunc = '';
    protected ?bool $readOnly = NULL;

    const VALID_TYPES = [
        '*', 'page', 'url', 'file', 'folder', 'email', 'telephone', 'record'
    ];

    private function _validateItems($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Radio' field '$identifier' configuration 'items' must be of type array, if set."
            );
        }
        $i = 0;
        foreach ($entry as $key => $value) {
            if (!is_string($key)) {
                throw new Exception (
                    "'Radio' field '$identifier' configuration 'items[$i]' key must be of type string, if set."
                );
            }
            if (!is_string($value) && !is_int($value)) {
                throw new Exception (
                    "'Radio' field '$identifier' configuration 'items[$i]' value must be of type string or integer, if set."
                );
            }
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
                        case 'items':
                            $this->_validateItems($value, $config);
                            $this->items = $value;
                            break;
                        case 'itemsProcFunc':
                            $this->validateUserFunc($value, $config, 'itemsProcFunc', 'Radio');
                            $this->itemsProcFunc = $value;
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
        return parent::_configToElement('radio', $properties);
    }
}

final class RadioField extends Fields
{
    protected RadioFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('radio', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new RadioFieldConfig();
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