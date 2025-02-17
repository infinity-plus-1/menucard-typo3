<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;

final class LinkFieldConfig extends Config
{
    protected string $default = '';
    protected bool $nullable = false;
    protected bool $required = false;
    protected array $allowedTypes = ['*'];

    const VALID_TYPES = [
        '*', 'page', 'url', 'file', 'folder', 'email', 'telephone', 'record'
    ];

    private function _validateAllowedTypes($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Link' field '$identifier' configuration 'allowedTypes' must be of type array, if set."
            );
        }
        foreach ($entry as $type) {
            if (!is_string($type)) {
                throw new Exception (
                    "'Link' field '$identifier' configuration 'allowedTypes[0-6]' entries must be of type string, if set."
                );
            }
            if ($type === '*' && count($entry) > 1) {
                throw new Exception (
                    "'Link' field '$identifier' configuration 'allowedTypes[]' found '*' as entry, but more keywords " .
                    "are declared. '*' already accepts all available keywords. Fix: Either remove '*' or all other keywords."
                );
            }
            if (!in_array($type, self::VALID_TYPES)) {
                throw new Exception (
                    "'Link' field '$identifier' configuration 'allowedTypes[]' allowed keywords are: '*', 'page', 'url', " .
                    "'file', 'folder', 'email', 'telephone', 'record'"
                );
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
                        case 'allowedTypes':
                            $this->_validateAllowedTypes($value, $config);
                            $this->allowedTypes = $value;
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
        return parent::_configToElement('link', $properties);
    }
}

final class LinkField extends Fields
{
    protected LinkFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('link', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new LinkFieldConfig();
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