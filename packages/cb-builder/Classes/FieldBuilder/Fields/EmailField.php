<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\Utility;
use Exception;

final class EmailFieldConfig extends Config
{
    protected bool $autocomplete = false;
    protected string $eval = '';
    protected string $mode = '';
    protected bool $nullable = false;
    protected string $placeholder = '';
    protected bool $readOnly = false;
    protected bool $required = false;
    protected int $size = 30;

    const EVAL_KEYWORDS = [
        'unique', 'uniqueInPid'
    ];

    private function _validateEval($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Email' field '$identifier' configuration 'eval' must be of type string, if set."
            );
        }
        if (!in_array($entry, self::EVAL_KEYWORDS)) {
            throw new Exception (
                "'Email' field '$identifier' configuration 'eval' must contain a specific keyword, if set. " .
                "Valid keywords are " . implode(', ', self::EVAL_KEYWORDS)
            );
        }
    }

    private function _validateMode($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Email' field '$identifier' configuration 'mode' must be of type string, if set."
            );
        }
        if ('useOrOverridePlaceholder' !== $entry) {
            throw new Exception (
                "'Email' field '$identifier' configuration 'mode' must contain the keyword 'useOrOverridePlaceholder', if set."
            );
        }
        if (!isset($config['placeholder'])) {
            throw new Exception (
                "'Email' field '$identifier' configuration 'mode' must takes only effect if a placeholder is defined as well, if set."
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
                        case 'eval':
                            $this->_validateEval($value, $config);
                            $this->eval = $value;
                            break;
                        case 'mode':
                            $this->_validateMode($value, $config);
                            $this->mode = $value;
                            break;
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'Email', 10, 50);
                            $this->size = $value;
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
        return parent::_configToElement('email', $properties);
    }
}

final class EmailField extends Fields
{
    protected EmailFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('email', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new EmailFieldConfig();
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