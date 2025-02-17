<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;

final class PasswordRules extends Config
{
    protected int $length = -1;
    protected ?bool $lowerCaseCharacters = NULL;
    protected string $random = '';
    protected ?bool $specialCharacters = NULL;
    protected ?bool $upperCaseCharacters = NULL;

    const RANDOM_KEYWORDS = [
        'base64', 'hex'
    ];

    private function _validateRandom($entry, array $identifier): void
    {
        $identifier = $identifier['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Password' field '$identifier' configuration 'passwordGenerator['options']['passwordRules']['random']' " .
                "must be of type string, if set."
            );
        }

        if (!in_array($entry, self::RANDOM_KEYWORDS)) {
            throw new Exception (
                "'Password' field '$identifier' configuration 'passwordGenerator['options']['passwordRules']['random']' " .
                "must contain a specific keyword, if set. " .
                "Valid keywords are: " . implode(', ', self::RANDOM_KEYWORDS)
            );
        }
    }


    public function arrayToConfig(array $config, array $identifier): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'length':
                        $this->validateInteger (
                            $value,
                            $identifier,
                            "passwordGenerator['options']['passwordRules']['length']",
                            'Password',
                            1,
                            PHP_INT_MAX
                        );
                        $this->length = intval($value);
                        break;
                    case 'random':
                        $this->_validateRandom($value, $identifier);
                        $this->random = $value;
                        break;
                    default:
                        $this->$configKey = $value;
                        break;
                }
            }
        }
    }
}

final class PasswordGeneratorOptionsConfig extends Config
{
    protected string $title = '';
    protected ?bool $allowEdit = NULL;
    protected ?PasswordRules $passwordRules = NULL;

    private function _validatePasswordRules($entry, array $identifier): void
    {
        $identifier = $identifier['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Password' field '$identifier' configuration 'passwordGenerator['options']['passwordRules']' must be of type array, if set."
            );
        }
    }

    public function arrayToConfig(array $config, array $identifier): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'passwordRules':
                        $this->_validatePasswordRules($value, $identifier);
                        $this->passwordRules = new PasswordRules();
                        $this->passwordRules->arrayToConfig($value, $identifier);
                        break;  
                    default:
                        $this->$configKey = $value;
                        break;
                }
            }
        }
    }
}

final class PasswordGeneratorConfig extends Config
{
    protected string $renderType = 'passwordGenerator';
    protected ?PasswordGeneratorOptionsConfig $options = NULL;

    private function _validatePasswordGeneratorOptions($entry, array $identifier): void
    {
        $identifier = $identifier['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Password' field '$identifier' configuration 'passwordGenerator['options']' must be of type array, if set."
            );
        }
    }

    public function arrayToConfig(array $config, array $identifier): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'options':
                        $this->_validatePasswordGeneratorOptions($value, $identifier);
                        $this->options = new PasswordGeneratorOptionsConfig();
                        $this->options->arrayToConfig($value, $identifier);
                        break;
                    default:
                        $this->$configKey = $value;
                        break;
                }
            }
        }
    }
}

final class PasswordFieldConfig extends Config
{
    protected ?bool $autocomplete = NULL;
    protected string $default = '';
    protected ?bool $hashed = true;
    protected string $mode = '';
    protected ?bool $nullable = NULL;
    protected ?PasswordGeneratorConfig $passwordGenerator = NULL;
    protected string $passwordPolicy = '';
    protected string $placeholder = '';
    protected ?bool $readOnly = NULL;
    protected ?bool $required = NULL;
    protected int $size = -1;

    private function _validatePasswordGenerator($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Password' field '$identifier' configuration 'passwordGenerator' must be of type array, if set."
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
                        case 'mode':
                            $this->validateMode($value, $config, 'Password');
                            $this->mode = $value;
                            break;
                        case 'passwordGenerator':
                            $this->_validatePasswordGenerator($value, $config);
                            $this->passwordGenerator = new PasswordGeneratorConfig();
                            $this->passwordGenerator->arrayToConfig($value, $config);
                            break;
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'Password', 10, 50);
                            $this->size = intval($value);
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
        return parent::_configToElement('password', $properties);
    }
}

final class PasswordField extends Fields
{
    protected PasswordFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('password', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new PasswordFieldConfig();
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