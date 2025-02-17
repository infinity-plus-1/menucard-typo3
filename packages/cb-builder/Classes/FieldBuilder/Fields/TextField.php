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
    protected int $size = 30;
    protected string $softref = '';
    protected array $valuePicker = [];

    const VALID_EVAL_KEYWORDS = [
        'alpha', 'alphanum', 'alphanum_x', 'domainname',
        'is_in', 'lower', 'md5', 'nospace', 'num', 'trim',
        'unique', 'uniqueInPid', 'upper', 'year'
    ];

    private function _validateEval($entry, $config): void
    {
        $identifier = $config['identifier'];
        $keywords = [];
        if (is_string($entry) && $entry !== '') {
            $keywords = array_map('trim', explode(',', $entry)); 
        }
        foreach ($keywords as $keyword) {
            if ($keyword === 'is_in' && !isset($config['is_in'])) {
                throw new Exception (
                    "'Text' field '$identifier' configuration 'eval' contains the keyword 'is_in', but 'is_in' is not " .
                    "declared in the Text field properties. Fix:\n...\ntype: Text\nis_in: abc123\neval: is_in\n..." 
                );
            }
            if (!in_array($keyword, self::VALID_EVAL_KEYWORDS, true)) {
                if (!str_contains($keyword, '::class') || !str_contains($keyword, '->')) {
                    throw new Exception (
                        "'Text' field '$identifier' configuration 'eval' must contain one of these keywords: 'alpha', 'alphanum', 'alphanum_x', " .
                        "'domainname', 'is_in', 'lower', 'md5', 'nospace', 'num', 'trim', 'unique', 'uniqueInPid', 'upper', 'year' " .
                        "or a path to a custom class, if set."
                    );
                }
            }
        }
    }

    private function _validateLength(array $config, int $length, string $field): void
    {
        $identifier = $config['identifier'];
        $properties = SimpleDatabaseQuery::getFieldProperties($config['identifier'], $config['useExistingField'], $config['table']);
        if (!empty($properties)) { // Does not exist, will be created with suitable length later.
            if ($length > $properties[0]['CHARACTER_MAXIMUM_LENGTH'] && strtolower($properties[0]['DATA_TYPE']) === 'varchar') {
                throw new Exception (
                    "'Text' field '$identifier' configuration '$field' exceeds VARCHARS set length."
                );
            }
        }
    }

    private function _validateMode($entry, $config): void
    {
        $identifier = $config['identifier'];
        if ($entry !== 'useOrOverridePlaceholder') {
            throw new Exception (
                "'Text' field '$identifier' configuration 'mode' must contain the value 'useOrOverridePlaceholder', if set."
            );
        }
        if (!isset($config['placeholder'])) {
            throw new Exception (
                "'Text' field '$identifier' configuration 'mode' needs to have 'placeholder' set, if set."
            );
        }
    }

    private function _validateSearch($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Text' field '$identifier' configuration 'search' must be of type array, if set."
            );
        }
        foreach ($entry as $key => $value) {
            switch ($key) {
                case 'pidonly':
                    if (!is_bool($value)) {
                        throw new Exception (
                            "'Text' field '$identifier' configuration 'search['pidonly']' must be of type boolean, if set."
                        );
                    }
                    break;
                case 'case':
                    if (!is_bool($value)) {
                        throw new Exception (
                            "'Text' field '$identifier' configuration 'search['case']' must be of type boolean, if set."
                        );
                    }
                    break;
                case 'andWhere':
                    if (!is_string($value)) {
                        throw new Exception (
                            "'Text' field '$identifier' configuration 'search['andWhere']' must be of type string, if set."
                        );
                    }
                    break;
                default:
                    throw new Exception (
                        "'Text' field '$identifier' configuration 'search['']' must either contain 'pidonly', 'case' or ; " .
                        "'andWhere', if set."
                    );
                    break;
            }
        }
    }

    private function _validateSoftRef($entry, $config): void
    {
        $identifier = $config['identifier'];
        $matches = [];
        preg_match("/\w+(\\[\w+(;\w+)*\\])?(,\w+(\\[\w+(;\w+)*\\])?)*/", $entry, $matches);
        if ($matches[0] !== $entry) {
            throw new Exception (
                "'Text' field '$identifier' configuration 'softref': Error in syntax. Syntax should look like: " .
                "key1,key2[parameter1;parameter2;...],..."
            );
        }
    }

    private function _validateValuePicker($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Number' field '$identifier' configuration 'valuePicker' must must be of type array, if set."
            );
        }
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
        if (isset($entry['items']) && is_array($entry['items'])) {
            $this->valuePicker['items'] = [];
            foreach ($entry['items'] as $item) {
                if (is_array($item) && count($item) === 1) {
                    if (is_string(array_key_first($item))) {
                        if (is_string(current($item)) || is_numeric(current($item)) || is_bool(current($item))) {
                            $this->valuePicker['items'][] = $item;
                        } else {
                            throw new Exception (
                                "'Number' field '$identifier' configuration 'valuePicker['items'][0-n][key => value]' must " .
                                "have a string, numeric or boolean value, if set."
                            );
                        }
                    } else {
                        throw new Exception (
                            "'Number' field '$identifier' configuration 'valuePicker['items'][0-n][key => value]' must " .
                            "have a string key, if set."
                        );
                    }
                } else {
                    throw new Exception (
                        "'Number' field '$identifier' configuration 'valuePicker['items'][0-n]' must be an array with exactly one entry." .
                        " E.g.:\nvaluePicker:\n  items:\n    - key1: value1\n    - key2: 2\n    - key3: 3.0\n    - key4: true"
                    );
                }
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
                        case 'default':
                            if ($GLOBALS['CbBuilder']['config']['propertySpecific']['Text']['default']['allowLongerThanMaxDbLen'] === false) {
                                $this->_validateLength($config, strlen($value), 'default');
                            }
                            $this->default = $value;
                            break;
                        case 'eval':
                            $this->_validateEval($value, $config);
                            $this->eval = $value;
                            break;
                        case 'max':
                            $this->_validateLength($config, $value, 'max');
                            $this->max = intval($value);
                            break;
                        case 'mode':
                            $this->_validateMode($value, $config);
                            $this->mode = $value;
                            break;
                        case 'search':
                            $this->_validateSearch($value, $config);
                            $this->search = $value;
                            break;
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'Text', 10, 50);
                            $this->size = intval($value);
                            break;
                        case 'softref':
                            $value = str_replace(' ', '', $value);
                            $this->_validateSoftRef($value, $config);
                            $this->softref = $value;
                            break;
                        case 'valuePicker':
                            $this->_validateValuePicker($value, $config);
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
        $field['identifier'] = $this->identifier;
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