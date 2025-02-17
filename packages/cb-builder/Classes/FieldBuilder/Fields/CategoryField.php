<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

final class CategoryFieldConfig extends Config
{
    protected string $default = '';
    protected string $exclusiveKeys = '';
    protected string $foreign_table = '';
    protected string $foreign_table_prefix = '';
    protected string $foreign_table_where = '';
    protected array $itemGroups = [];
    protected int $maxitems = -1;
    protected int $minitems = -1;
    protected string $MM = '';
    protected bool $readOnly = false;
    protected string $relationship = '';
    protected int $size = -1;
    protected array $treeConfig = [];

    const RELATIONSHIP_KEYWORDS = [
        'oneToOne', 'oneToMany', 'manyToMany'
    ];

    const TREECONFIG_KEYWORDS = [
        'dataProvider' => parent::STRING_TYPE,
        'childrenField' => parent::STRING_TYPE,
        'parentField' => parent::STRING_TYPE,
        'startingPoints' => parent::STRING_TYPE,
        'appearance' => parent::FUNCTION,
    ];

    const TREECONFIG_APPEARANCE_KEYWORDS = [
        'showHeader' => parent::BOOL_TYPE,
        'expandAll' => parent::BOOL_TYPE,
        'maxLevels' => parent::INTEGER_TYPE,
        'nonSelectableLevels' => parent::STRING_TYPE,
    ];

    private function _validateTreeConfigOption_appearance($appearance, $identifier): void
    {
        if (!is_array($appearance)) {
            throw new Exception (
                "'Category' field '$identifier' configuration 'treeConfig['appearance']' must be of type array, if set."
            );
        }
        $i = 0;
        foreach ($appearance as $key => $value) {
            if (!array_key_exists($key, self::TREECONFIG_APPEARANCE_KEYWORDS)) {
                throw new Exception (
                    "'Category' field '$identifier' configuration 'treeConfig['appearance'][$i]' $key is no valid keyword. " .
                    "Valid keywords are: " . implode(', ', array_keys(self::TREECONFIG_APPEARANCE_KEYWORDS))
                );
            }
            switch (self::TREECONFIG_APPEARANCE_KEYWORDS[$key]) {
                case parent::BOOL_TYPE:
                    if (!is_bool($value)) {
                        throw new Exception (
                            "'Category' field '$identifier' configuration 'treeConfig['appearance']['$key']' value must be of type " .
                            "boolean, if set."
                        );
                    }
                    break;
                case parent::INTEGER_TYPE:
                    if (!$this->handleIntegers($value)) {
                        throw new Exception (
                            "'Category' field '$identifier' configuration 'treeConfig['appearance']['$key']' value must be of type " .
                            "integer or a string representing an integer, if set."
                        );
                    }
                    break;
                case parent::STRING_TYPE:
                    if (!is_string($value)) {
                        throw new Exception (
                            "'Category' field '$identifier' configuration 'treeConfig['appearance']['$key']' value must be of type " .
                            "string, if set."
                        );
                    }
                    break;
            }

            if ($key === 'nonSelectableLevels') {
                $splitted = GeneralUtility::trimExplode(',', $value);
                $j = 0;
                foreach ($splitted as $num) {
                    if ($this->handleIntegers($num) === NULL) {
                        throw new Exception (
                            "'Category' field '$identifier' configuration 'treeConfig['appearance']['nonSelectableLevels'][$j]' value " .
                            "must be of type string representing an integer, if set."
                        );
                    }
                    $j++;
                }
            }
            $i++;
        }
    }

    private function _validateExclusiveKeys($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Category' field '$identifier' configuration 'exclusiveKeys' must be of type string, if set."
            );
        }
        $splitted = GeneralUtility::trimExplode(',', $entry);

        $i = 0;
        $splitted = array_walk($splitted, function ($v) use ($identifier, &$i) {
            if (!$v = $this->handleIntegers($v)) {
                throw new Exception (
                    "'Category' field '$identifier' configuration 'exclusiveKeys[$i]' must be of type integer or a string representing an " .
                    "integer, if set."
                );
            }
            $i++;
            return $v;
        });
    }

    private function _validateForeignTable($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!$this->tableExists($entry, $config)) {
            throw new Exception (
                "'Category' field '$identifier' configuration 'foreign_table' table '$entry' does not exist and will not " .
                "be created by the builder. Fix: Check for typos, choose another table, create the table manually or add " .
                "a Collection with an identifier identical to the table name."
            );
        }
    }

    private function _validateItemGroups($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Category' field '$identifier' configuration 'itemGroups' must be of type array, if set."
            );
        }
        $i = 0;
        foreach ($entry as $key => $value) {
            if (!is_string($key) && !is_int($key)) {
                throw new Exception (
                    "'Category' field '$identifier' configuration 'itemGroups[$i]' key must be of type string or integer, if set."
                );
            }
            if (!is_string($value)) {
                throw new Exception (
                    "'Category' field '$identifier' configuration 'itemGroups[$i]' value must be of type string, if set."
                );
            }
            $i++;
        }
    }

    

    private function _validateRelationship($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Category' field '$identifier' configuration 'relationshop' must be of type string, if set."
            );
        }

        if (!in_array($entry, self::RELATIONSHIP_KEYWORDS)) {
            throw new Exception (
                "'Category' field '$identifier' configuration 'relationship' must be one " .
                "of the following keywords: " . implode(', ', self::RELATIONSHIP_KEYWORDS)
            );
        }
    }

    private function _validateTreeConfig($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Category' field '$identifier' configuration 'treeconfig' must be of type array, if set."
            );
        }

        $i = 0;
        foreach ($entry as $key => $value) {
            if (!array_key_exists($key, self::TREECONFIG_KEYWORDS)) {
                throw new Exception (
                    "'Category' field '$identifier' configuration 'treeConfig[$i]' $key is no valid keyword. " .
                    "Valid keywords are: " . implode(', ', array_keys(self::TREECONFIG_KEYWORDS))
                );
            }

            switch (self::TREECONFIG_KEYWORDS[$key]) {
                case parent::STRING_TYPE:
                    if (!is_string($value)) {
                        throw new Exception (
                            "'Category' field '$identifier' configuration 'treeConfig['$key']' value must be of type " .
                            "string, if set."
                        );
                    }
                    break;
                case parent::FUNCTION:
                    $function = '_validateTreeConfigOption_' . $key;
                    call_user_func([$this, $function], $value, $identifier);
                    break;
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
                        case 'exclusiveKeys':
                            $this->_validateExclusiveKeys($value, $config);
                            $this->exclusiveKeys = $value;
                            break;
                        case 'foreign_table':
                            $this->_validateForeignTable($value, $config);
                            $this->foreign_table = $value;
                            break;
                        case 'itemGroups':
                            $this->_validateItemGroups($value, $config);
                            $this->itemGroups = $value;
                            break;
                        case 'maxitems':
                            $this->validateInteger($value, $config, 'maxitems', 'Category', 1, PHP_INT_MAX, true, true, 'minitems');
                            $this->maxitems = intval($value);
                            break;
                        case 'minitems':
                            $this->validateInteger($value, $config, 'minitems', 'Category', 1, PHP_INT_MAX, true, false, 'maxitems');
                            $this->minitems = intval($value);
                            break;
                        case 'relationship':
                            $this->_validateRelationship($value, $config);
                            $this->relationship = $value;
                            break;
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'Category', 1, PHP_INT_MAX);
                            $this->size = intval($value);
                            break;
                        case 'treeConfig':
                            $this->_validateTreeConfig($value, $config);
                            $this->treeConfig = $value;
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
        return parent::_configToElement('category', $properties);
    }
}

final class CategoryField extends Fields
{
    protected CategoryFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('category', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new CategoryFieldConfig();
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