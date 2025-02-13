<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use Exception;

final class TextFieldConfig extends Config
{
    protected bool $autocomplete = false;
    protected string $default = '';
    protected string $eval = '';
    protected string $is_in = '';
    protected int $max = -1;
    protected int $min = -1;
    protected string $mode = '';
    protected bool $nullable = false;
    protected string $placeholder = '';
    protected bool $readOnly = false;
    protected bool $required = false;
    protected array $search = [];
    protected int $size = -1;
    protected string $softref = '';
    protected array $valuePicker = [];

    const VALID_EVAL_KEYWORDS = [
        'alpha', 'alphanum', 'alphanum_x', 'domainname',
        'is_in', 'lower', 'md5', 'nospace', 'num', 'trim',
        'unique', 'uniqueInPid', 'upper', 'year'
    ];

    private function _validateEval($entry): void
    {
        $keywords = [];
        if (is_string($entry) && $entry !== '') {
            $keywords = array_map('trim', explode(',', $entry)); 
        }
        foreach ($keywords as $keyword) {
            if (!in_array($keyword, self::VALID_EVAL_KEYWORDS, true)) {
                if (!str_contains($keyword, '::class') || !str_contains($keyword, '->')) {
                    throw new Exception (
                        "'Text' field configuration 'eval' must contain one of these keywords: 'alpha', 'alphanum', 'alphanum_x', " .
                        "'domainname', 'is_in', 'lower', 'md5', 'nospace', 'num', 'trim', 'unique', 'uniqueInPid', 'upper', 'year' " .
                        "or a path to a custom class, if set."
                    );
                }
            }
        }
    }

    private function _validateLength(array $config, int $length, string $field): void
    {
        $properties = SimpleDatabaseQuery::getFieldProperties($config['identifier'], $config['useExistingField'], $config['table']);
        if (!empty($properties)) { // Does not exist, will be created with suitable length later.
            if ($length > $properties[0]['CHARACTER_MAXIMUM_LENGTH'] && strtolower($properties[0]['DATA_TYPE']) === 'varchar') {
                throw new Exception (
                    "'Text' field configuration '$field' exceeds VARCHARS set length."
                );
            }
        }
    }

    private function _validateMode($entry, $config): void
    {
        if ($entry !== 'useOrOverridePlaceholder') {
            throw new Exception (
                "'Text' field configuration 'mode' must contain the value 'useOrOverridePlaceholder', if set."
            );
        }
        if (!isset($config['placeholder'])) {
            throw new Exception (
                "'Text' field configuration 'mode' needs to have 'placeholder' set, if set."
            );
        }
    }

    private function _validateSearch($entry): void
    {
        if (!is_array($entry)) {
            throw new Exception (
                "'Text' field configuration 'search' must be of type array, if set."
            );
        }
        foreach ($entry as $key => $value) {
            switch ($key) {
                case 'pidonly':
                    if (!is_bool($value)) {
                        throw new Exception (
                            "'Text' field configuration 'search['pidonly']' must be of type boolean, if set."
                        );
                    }
                    break;
                case 'case':
                    if (!is_bool($value)) {
                        throw new Exception (
                            "'Text' field configuration 'search['case']' must be of type boolean, if set."
                        );
                    }
                    break;
                case 'andWhere':
                    if (!is_string($value)) {
                        throw new Exception (
                            "'Text' field configuration 'search['andWhere']' must be of type string, if set."
                        );
                    }
                    break;
                default:
                    throw new Exception (
                        "'Text' field configuration 'search['']' must either be 'pidonly', 'case' or ; " .
                        "'andWhere', if set."
                    );
                    break;
            }
        }
    }

    private function _validateSize($entry): void
    {
        if (($entry = filter_var($entry, FILTER_SANITIZE_NUMBER_INT)) !== false) {
            throw new Exception (
                "'Text' field configuration 'size' must be of type integer, if set."
            );
        }
        $entry = intval($entry);
        if ($entry < 10 || $entry > 50) {
            throw new Exception (
                "'Text' field configuration 'size' must be a range between 10 and 50, if set."
            );
        }
    }

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'default':
                        if (
                            $GLOBALS['CbBuilder']['config']['Strict'] === true
                            && $GLOBALS['CbBuilder']['config']['propertySpecific']['Text']['default']['allowLongerThanMaxDbLen'] === false
                        ) {
                            $this->_validateLength($config, strlen($value), 'default');
                        }
                        $this->default = $value;
                        break;
                    case 'eval':
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) $this->_validateEval($value);
                        $this->eval = $value;
                        break;
                    case 'max':
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            $this->_validateLength($config, $value, 'max');
                        }
                        $this->max = intval($value);
                        break;
                    case 'mode':
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            $this->_validateMode($value, $config);
                        }
                        $this->mode = $value;
                        break;
                    case 'search':
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            $this->_validateSearch($value);
                        }
                        $this->mode = $value;
                        break;
                    case 'size':
                        if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                            $this->_validateSize($value);
                        }
                        $this->mode = $value;
                        break;
                    default:
                        $this->$configKey = $value;
                        break;
                }
                
            }
        }
    }

    public function configToElement(): array
    {
        $properties = get_object_vars($this);
        return parent::_configToElement('input', $properties);
    }
}

final class TextField extends Fields
{
    protected TextFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('input', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $config = new TextFieldConfig();
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