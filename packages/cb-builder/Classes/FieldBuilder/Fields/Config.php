<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\CbBuilder\FieldBuilder\FieldBuilder;
use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use Exception;

class ConfigException extends Exception {}

class Config
{
    protected array $behaviour;
    protected array $fieldControl;
    protected array $fieldInformation;
    protected array $fieldWizard;

    const BOOL_TYPE = 1;
    const STRING_TYPE = 2;
    const INTEGER_TYPE = 3; //Not in use yet
    const FLOAT_TYPE = 4; //Not in use yet
    const FUNCTION = 5;

    protected function validateTable(mixed $table, array $config, string $setting, string $type): void
    {
        $identifier = $config['identifier'];
        if (!is_string($table)) {
            throw new Exception (
                "'$type' field '$identifier' configuration '$setting' must be of type string, if set."
            );
        }
        $sdq = new SimpleDatabaseQuery();
        if (!FieldBuilder::isSurpressedWarning(152082918) && !$sdq->tableExists($table) && !FieldBuilder::fieldExists($table, 'Collection')) {
            throw new Exception (
                "WARNING: 'Group' field '$identifier' configuration '$setting' table '$table' does neither exist in the db nor " .
                "it will be created in this process.\n" .
                "You can surpress this warning in the cbconfig.yaml by adding the code 152082918 to surpressWarning."
            );
        }
    }

    protected function validateField(mixed $field, array $config, string $setting, string $type, array $tables = ['*']): void
    {
        $identifier = $config['identifier'];
        if (!is_string($field)) {
            throw new Exception (
                "'$type' field '$identifier' configuration '$setting' must be of type string, if set."
            );
        }
        if (empty($tables)) {
            $tables[0] = '*';
        }
        $sdq = new SimpleDatabaseQuery();
        if (
                !FieldBuilder::isSurpressedWarning(152082917)
                && !$sdq->fieldExists($field, $tables)
                && !FieldBuilder::fieldExists($field, '', 'Pass, Palette')
        ) {
            $msg = isset($tables[0]) && $tables[0] === '*' ? "db" : "table(s) '" . implode(', ', $tables) . "'";
            throw new Exception (
                "WARNING: 'Group' field '$identifier' configuration '$setting' field '$field' does neither exist in the $msg nor " .
                "it will be created in this process.\n" .
                "You can surpress this warning in the cbconfig.yaml by adding the code 152082917 to surpressWarning."
            );
        }
    }

    protected function validateArrayStringString(mixed $array, array $config, string $setting, string $type): void
    {
        $identifier = $config['identifier'];
        if (!is_array($array)) {
            throw new Exception (
                "'$type' field '$identifier' configuration '$setting' must be of type array, if set."
            );
        }

        $i = 0;
        foreach ($array as $key => $value) {
            if (!is_string($key)) {
                throw new Exception (
                    "'$type' field '$identifier' configuration '$setting [$i]' key must be of type string, if set."
                );
            }
            if (!is_string($value)) {
                throw new Exception (
                    "'$type' field '$identifier' configuration '$setting [$i]' value must be of type string, if set."
                );
            }
            $i++;
        }
    }

    protected function validateUserFunc(mixed $entry, array $config, string $setting, string $type): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'$type' field '$identifier' configuration '$setting' must be of type string, if set."
            );
        }

        if (!str_contains($entry, '->')) {
            throw new Exception (
                "'$type' field '$identifier' configuration '$setting' must be in format " .
                "\Vendor\Extension\UserFunction\ClassName -> method, if set."
            );
        }
    }

    protected function validateMode(mixed $entry, array $config, string $type): void
    {
        $identifier = $config['identifier'];

        if (!is_string($entry)) {
            throw new Exception (
                "'$type' field '$identifier' configuration 'mode' must be of type string, if set."
            );
        }

        if ($entry !== 'useOrOverridePlaceholder') {
            throw new Exception (
                "'$type' field '$identifier' configuration 'mode' must contain the value 'useOrOverridePlaceholder', if set."
            );
        }
        if (!isset($config['placeholder'])) {
            throw new Exception (
                "'$type' field '$identifier' configuration 'mode' needs to have 'placeholder' set, if set."
            );
        }
    }

    protected function validateInteger (
        mixed $entry,
        array $config,
        string $_config,
        string $type,
        int $min,
        int $max,
        ?bool $isMinMax = false,
        ?bool $isMax = false,
        ?string $minOrMaxIdentifier = ''
    ): void {
        $identifier = $config['identifier'];
        if (($num = $this->handleIntegers($entry)) !== NULL) {
            if ($num < $min || $num > $max) {
                if ($num < $min) {
                    $min--;
                    throw new Exception (
                        "'$type' field '$identifier' configuration '$_config' must be an integer greater than $min, if set."
                    );
                } else {
                    $max++;
                    throw new Exception (
                        "'$type' field '$identifier' configuration '$_config' must be an integer smaller than $max, if set."
                    );
                }
            }
        } else {
            throw new Exception (
                "'$type' field '$identifier' configuration '$_config' must be of type integer or " .
                "a string that represents an integer number, if set. $_config must be in a range between $min and $max."
            );
        }

        if ($isMinMax && $minOrMaxIdentifier !== '' && isset($config[$minOrMaxIdentifier])) {
            $minOrMax = $config[$minOrMaxIdentifier];
            if (($minOrMax = $this->handleIntegers($minOrMax)) === NULL) {
                throw new Exception (
                    "'$type' field '$identifier' configuration '$minOrMaxIdentifier' must be of type integer or " .
                    "a string that represents an integer number, if set."
                );
            }
            if ($isMax) {
                
                if ($num < $minOrMax) {
                    throw new Exception (
                        "'$type' field '$identifier' configuration '$_config' must be greater than or equal to '$minOrMaxIdentifier', if set."
                    );
                }
            } else {
                if ($num > $minOrMax) {
                    throw new Exception (
                        "'$type' field '$identifier' configuration '$_config' must be lesser than or equal to '$minOrMaxIdentifier', if set."
                    );
                }
            }
        }
    }

    protected function tableExists(string $table, array $config): bool
    {
        $sdq = new SimpleDatabaseQuery();
        if (!$sdq->tableExists($table)) {
            $fields = $GLOBALS['CbBuilder']['fields'];
            foreach ($fields as $value) {
                if (isset($value['type']) && $value['type'] === 'Collection') {
                    if (isset($value['identifier']) && $value['identifier'] === $table) {
                        return true;
                    }
                }
            }
        } else {
            return true;
        }
        return false;
    }

    protected function handleIntegers(mixed $integer): int|NULL
    {
        if (is_int($integer)) return $integer;
        if (is_float($integer)) return intval($integer);
        if ($GLOBALS['CbBuilder']['config']['autoSanitizeInteger'] === false) {
            if (!is_numeric($integer) || (string)(int)$integer !== $integer) {
                return NULL;
            }
        }
        $integer = filter_var($integer, FILTER_SANITIZE_NUMBER_INT);
        return $integer !== false ? intval($integer) : NULL;
    }

    protected function isValidLeveledConfig(array $allowed, string $config, string $type): bool
    {
        if (array_key_exists($config, $allowed) && (in_array('all', $allowed[$config]) || in_array($type, $allowed[$config]))) {
            return true;
        }
        return false;
    }

    protected function isValidConfig(array $properties, string $config): bool
    {
        // if (array_key_exists($config, $properties)) {
        //     if (is_array())
        // }
        return array_key_exists($config, $properties);
    }

    

    protected function _configToElement(string $type, array $properties): array
    {
        $config = [
            'type' => $type
        ];
        foreach ($properties as $property => $value) {
            if (is_string($value)) {
                if ($value != '') $config[$property] = $value;
            } else if (is_numeric($value)) {
                if ($value >= 0) $config[$property] = $value;
            } else if (is_array($value)) {
                if (!empty($value)) $config[$property] = $value;
            }
            else $config[$property] = $value;
        }
        return $config;
    }

    protected function setBehaviour(array $behaviour, string $type): void
    {
        $allowed = [
            'allowLanguageSynchronization' => [
                'all'
            ]
        ];
        foreach ($behaviour as $config => $value) {
            if ($this->isValidLeveledConfig($allowed, $config, $type)) {
                $this->behaviour[$config] = $value;
            }
        }
    }

    public function getBehaviour(): array
    {
        return $this->behaviour;
    }
}