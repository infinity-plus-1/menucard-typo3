<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;

final class DatetimeFieldConfig extends Config
{
    protected string $dbType = '';
    protected string|int $default = '';
    protected bool $disableAgeDisplay = false;
    protected string $format = '';
    protected string $mode = '';
    protected bool $nullable = false;
    protected string|int $placeholder = '';
    protected array $range = [];
    protected bool $readOnly = false;
    protected array $search = [];
    protected string $softref = '';

    const DB_TYPES = [
        'date', 'time', 'datetime'
    ];

    const FORMATS = [
        'date', 'time', 'datetime', 'timesec'
    ];

    const RANGE_KEYS = [
        'lower', 'upper'
    ];

    private function _validateDate($datetime, $identifier, $config): void
    {
        $match = [];
        preg_match (
            "/(\d{4}-\d{2}-\d{2}|\d{4}\/\d{2}\/\d{2}|\d{4}\\.\d{2}\\.\d{2}|\d{2}-\d{2}-\d{4}|" .
            "\d{2}\/\d{2}\/\d{4}|\d{2}\\.\d{2}\\.\d{4})(?:\s\d{2}:\d{2}:\d{2})?/", $datetime, $match
        );
        
        if ($match[0] !== $datetime) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration '$config' does not contain a valid format, valid datetime formats are: " .
                "'YYYY/MM/DD HH:MM:SS', 'YYYY/MM/DD', 'YYYY-MM-DD HH:MM:SS', 'YYYY-MM-DD', " .
                "'YYYY.MM.DD HH:MM:SS', 'YYYY.MM.DD', 'DD/MM/YYYY HH:MM:SS', 'DD/MM/YYYY', " .
                "'DD-MM-YYYY HH:MM:SS', 'DD-MM-YYYY', 'DD.MM.YYYY HH:MM:SS' and 'DD.MM.YYYY'"
            );
        }
    }

    private function _convertToUnix($datetime, $config, $_config): int
    {
        $identifier = $config['identifier'];

        $datetime = explode(' ', $datetime);

        $count = count($datetime);
        if ($count !== 1 && $count !== 2) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration '$_config' does not contain a valid format, valid datetime formats are: " .
                "'YYYY/MM/DD HH:MM:SS', 'YYYY/MM/DD', 'YYYY-MM-DD HH:MM:SS', 'YYYY-MM-DD', " .
                "'YYYY.MM.DD HH:MM:SS', 'YYYY.MM.DD', 'DD/MM/YYYY HH:MM:SS', 'DD/MM/YYYY', " .
                "'DD-MM-YYYY HH:MM:SS', 'DD-MM-YYYY', 'DD.MM.YYYY HH:MM:SS' and 'DD.MM.YYYY'"
            );
        }
        $date = $datetime[0];
        $time = $count === 2 ? $datetime[1] : '';
        
        $hour = $min = $sec = 0;
        if ($time !== '') {
            $time = explode(':', $time);
            if (count($time) !== 3) {

            }
            $hour = intval($time[0]);
            $min = intval($time[1]);
            $sec = intval($time[2]);
        }

        $year = $mon = $day = 0;
        $sep = str_contains($date, '/') ? '/' : (str_contains($date, '-') ? '-' : '.');
        $date = explode($sep, $date);

        if (count($date) !== 3) {

        }

        $year = strlen($date[0]) === 4 ? intval($date[0]) : (strlen($date[2]) === 4 ? intval($date[2]) : NULL);
        if ($year === NULL) {

        }
        $mon = intval($date[1]);
        $day = strlen($date[2]) === 2 ? intval($date[2]) : (strlen($date[0]) === 2 ? intval($date[0]) : NULL);
        if ($day === NULL) {

        }

        return gmmktime($hour, $min, $sec, $mon, $day, $year);
    }

    private function _validateDbType($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'dbType' must be of type string, if set."
            );
        }
        if (!in_array($entry, self::DB_TYPES)) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'dbType' must be one of these keywords, if set: " .
                implode(', ', self::DB_TYPES)
            );
        } 
    }

    private function _validateDefaultOrPlaceholder($entry, $config, string $_config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration '$_config' must be of type string, if set."
            );
        }

        $this->_validateDate($entry, $identifier, 'default');
    }

    private function _validateFormat($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'format' must be of type string, if set."
            );
        }

        if (!isset($config['readOnly']) || !$config['readOnly']) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'format' takes only effect if the config 'readOnly' is set to true. " .
                "Fix: Omit the format or set ['config']['readOnly'] to true."
            );
        }

        if (!in_array($entry, self::FORMATS)) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'format' must be one of these keywords, if set: " .
                implode(', ', self::FORMATS)
            );
        } 
    }

    private function _validateMode($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'mode' must be of type string, if set."
            );
        }
        if ('useOrOverridePlaceholder' !== $entry) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'mode' must contain the keyword 'useOrOverridePlaceholder', if set."
            );
        }
        if (!isset($config['placeholder'])) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'mode' must takes only effect if a placeholder is defined as well."
            );
        }
    }

    private function _validateRange($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'range' must be of type array, if set."
            );
        }
        $count = count($entry);
        if ($count !== 1 && $count !== 2) {
            throw new Exception (
                "'Datetime' field '$identifier' configuration 'range' must contain one or two elements, if set."
            );
        }

        foreach ($entry as $key => $value) {
            if (!in_array($key, self::RANGE_KEYS)) {
                throw new Exception (
                    "'Datetime' field '$identifier' configuration 'range' must contain a valid key, if set. " .
                    "Valid keys are: " . implode(', ', self::RANGE_KEYS)
                );
            }
            $this->_validateDate($value, $identifier, "range[$key]");
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

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    switch ($configKey) {
                        case 'dbType':
                            $this->_validateDbType($value, $config);
                            $this->dbType = $value;
                            break;
                        case 'default':
                            $this->_validateDefaultOrPlaceholder($value, $config, 'default');
                            $this->default = $this->_convertToUnix($value, $config, 'default');
                            break;
                        case 'format':
                            $this->_validateFormat($value, $config);
                            $this->format = $value;
                            break;
                        case 'mode':
                            $this->_validateMode($value, $config);
                            $this->mode = $value;
                            break;
                        case 'placeholder':
                            $this->_validateDefaultOrPlaceholder($value, $config, 'placeholder');
                            $this->placeholder = $this->_convertToUnix($value, $config, 'placeholder');
                            break;
                        case 'range':
                            $this->_validateRange($value, $config);
                            if (isset($value['upper'])) $value['upper'] = $this->_convertToUnix($value['upper'], $config, "range['upper']");
                            if (isset($value['lower'])) $value['lower'] = $this->_convertToUnix($value['lower'], $config, "range['lower']");
                            if ($value['upper'] < $value['lower']) {
                                $identifier = $config['identifier'];
                                throw new Exception (
                                    "'Text' field '$identifier' configuration 'range': 'upper' can't be earlier than 'lower'. " .
                                    "Fix: Swap values."
                                );
                            }
                            $this->range = $value;
                            break;
                        case 'search':
                            $this->_validateSearch($value, $config);
                            $this->search = $value;
                            break;
                        case 'softref':
                            $value = str_replace(' ', '', $value);
                            $this->_validateSoftRef($value, $config);
                            $this->softref = $value;
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
        return parent::_configToElement('datetime', $properties);
    }
}

final class DatetimeField extends Fields
{
    protected DatetimeFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('datetime', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new DatetimeFieldConfig();
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