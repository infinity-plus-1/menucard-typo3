<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;

final class UuidFieldConfig extends Config
{
    protected ?bool $enableCopyToClipboard = NULL;
    protected ?bool $required = NULL;
    protected int $size = -1;
    protected int $version = -1;

    const VALID_VERSIONS = [
        4, 6, 7
    ];

    private function _validateVersion($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (($entry = $this->handleIntegers($entry)) === NULL) {
            throw new Exception (
                "'Uuid' field '$identifier' configuration 'version' must be of type integer, if set."
            );
        }
        if (!in_array($entry, self::VALID_VERSIONS)) {
            throw new Exception (
                "'Uuid' field '$identifier' configuration 'version' $entry is no valid value.\n" .
                "Valid values are: " . implode(',', self::VALID_VERSIONS)
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
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'Uuid', 10, 50);
                            $this->size = $value;
                            break;
                        case 'version':
                            $this->_validateVersion($value, $config);
                            $this->version = $value;
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
        return parent::_configToElement('uuid', $properties);
    }
}

final class UuidField extends Fields
{
    protected UuidFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('uuid', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new UuidFieldConfig();
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