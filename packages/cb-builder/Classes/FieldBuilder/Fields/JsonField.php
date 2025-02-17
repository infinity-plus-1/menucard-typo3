<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;

final class JsonFieldConfig extends Config
{
    protected int $cols = 30;
    protected string $default = '';
    protected bool $enableCodeEditor = false;
    protected string $placeholder = '';
    protected bool $readOnly = false;
    protected bool $required = false;
    protected int $rows = 5;

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    switch ($configKey) {
                        case 'cols':
                            $this->validateInteger($value, $config, 'cols', 'Json', 1, 50);
                            $this->cols = $value;
                            break;
                        case 'rows':
                            $this->validateInteger($value, $config, 'rows', 'Json', 1, 20);
                            $this->rows = $value;
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
        return parent::_configToElement('json', $properties);
    }
}

final class JsonField extends Fields
{
    protected JsonFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('json', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new JsonFieldConfig();
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