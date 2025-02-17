<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\CbBuilder\FieldBuilder\FieldBuilder;
use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class GroupFieldSuggestOptionsConfig extends Config
{
    protected string $key = '';
    protected string $additionalSearchFields = ''; //column name
    protected string $addWhere = '';
    protected string $cssClass = '';
    protected int $maxItemsInResultList = -1;
    protected int $maxPathTitleLength = -1;
    protected int $minimumCharacters = -1;
    protected string $orderBy = '';
    protected string $pidList = '';
    protected int $pidDepth = -1;
    protected string $receiverClass = '';
    protected string $renderFunc = '';
    protected string $searchCondition = '';
    protected ?bool $searchWholePhrase = NULL;

    private function _validateAdditionalSearchFields(mixed $fields, array $config): void
    {
        $identifier = $config['identifier'];
        $key = $this->key;
        if (!is_string($fields)) {
            throw new Exception (
                "'Group' field '$identifier' configuration 'suggestOptions['$key']['additionalSearchFields']' must be of type string, if set."
            );
        }

        $fields = GeneralUtility::trimExplode(',', $fields);

        $i = 0;
        foreach ($fields as $field) {
            try {
                $this->validateField($field, $config, "suggestOptions['$key']['additionalSearchFields'][$i]", 'Group');
            } catch (\Throwable $th) {
                throw new Exception ($th->getMessage() . "\nFix: suggestOptions:\n  $key:\n    additionalSearchFields: 'nav_title, url, " .
                "or_any_valid_table_field'");
            }
            $i++;
        }
    }

    private function _validateMinimumCharacters(mixed $value, array $config): void
    {
        $identifier = $config['identifier'];
        $key = $this->key;
        $this->validateInteger($value, $config, "suggestOptions['$key']['minimumCharacters']", 'Group', 1, PHP_INT_MAX);
        if ($key !== 'default') {
            throw new Exception (
                "'Group' field '$identifier' configuration 'suggestOptions['$key']['minimumCharacters']' works only in the " .
                "default configuration array suggestOptions['default']['minimumCharacters'], if set.\n" .
                "Fix: suggestOptions:\n  default:\n    minimumCharacters: 3"
            );
        }
    }

    private function _validateAndSetPidList(mixed $value, array $config): void
    {
        $identifier = $config['identifier'];
        $key = $this->key;
        if (!is_string($value)) {
            throw new Exception (
                "'Group' field '$identifier' configuration 'suggestOptions['$key']['pidList']' must be of type string, if set."
            );
        }

        $splitted = GeneralUtility::trimExplode(',', $value);
        $pids = [];
        $i = 0;
        foreach ($splitted as $pid) {
            if (($pid = $this->handleIntegers($pid)) === NULL) {
                throw new Exception (
                    "'Group' field '$identifier' configuration 'suggestOptions['$key']['pidList'][$i]' must be of type " .
                    "integer or a string representing an integer, if set."
                );
            }
            $pids[$i++] = $pid;
        }
    }

    public function arrayToConfig(array $config, array $globalConf, string $key): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'additionalSearchFields':
                        $this->_validateAdditionalSearchFields($value, $globalConf);
                        $this->additionalSearchFields = $value;
                        break;
                    case 'maxItemsInResultList':
                        $this->validateInteger($value, $globalConf, "suggestOptions['$key']['maxItemsInResultList']", 'Group', 1, PHP_INT_MAX);
                        $this->additionalSearchFields = $value;
                        break;
                    case 'maxPathTitleLength':
                        $this->validateInteger($value, $globalConf, "suggestOptions['$key']['maxPathTitleLength']", 'Group', 1, PHP_INT_MAX);
                        $this->additionalSearchFields = $value;
                        break;
                    case 'minimumCharacters':
                        $this->_validateMinimumCharacters($value, $globalConf);
                        $this->additionalSearchFields = $value;
                        break;
                    case 'pidList':
                        $this->_validateAndSetPidList($value, $globalConf);
                        break;
                    case 'renderFunc':
                        $this->validateUserFunc($value, $globalConf, "suggestOptions['$key']['renderFunc']", 'Group');
                        break;
                    default:
                        $this->$configKey = $value;
                        break;
                }
            }
        }
    }
}

final class GroupFieldConfig extends Config
{
    protected string $allowed = '';
    protected int $autoSizeMax = -1;
    protected string $default = '';
    protected array $elementBrowserEntryPoints = [];
    protected array $filter = [];
    protected string $foreign_table = '';
    protected ?bool $hideDeleteIcon = NULL;
    protected ?bool $hideMoveIcons = NULL;
    protected ?bool $hideSuggest = NULL;
    protected ?bool $localizeReferencesAtParentLocalization = NULL;
    protected int $maxitems = -1;
    protected int $minitems = -1;
    protected string $MM = '';
    protected array $MM_match_fields = [];  //stringstring array key ist field (column)
    protected string $MM_opposite_field = '';
    protected array $MM_oppositeUsage = []; //string key ist field (column) und value string/arrayofstrings
    protected string $MM_table_where = '';
    protected ?bool $multiple = NULL;
    protected ?bool $prepend_tname = NULL;
    protected ?bool $readOnly = NULL;
    protected int $size = -1;
    protected ?GroupFieldSuggestOptionsConfig $suggestOptions = NULL;

    const FILTER_TYPES = [
        'userFunc' => parent::FUNCTION,
        'parameters' => parent::FUNCTION
    ];

    private function _validateTableList(mixed $entry, array $config, string $setting): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Group' field '$identifier' configuration 'allowed' must be of type string, if set."
            );
        }
        $splitted = GeneralUtility::trimExplode(',', $entry);
        $sdq = new SimpleDatabaseQuery();
        foreach ($splitted as $field) {
            if (!FieldBuilder::isSurpressedWarning(152082917) && !$sdq->fieldExists($field) && !FieldBuilder::fieldExists($field, 'Collection')) {
                throw new Exception (
                    "WARNING: 'Group' field '$identifier' configuration '$setting' field '$field' does neither exist in the db nor " .
                    "it will be created in this process.\n" .
                    "You can surpress this warning in the cbconfig.yaml by adding the code 152082917 to surpressWarning."
                );
            }
        }
    }

    private function _validateAutoSizeMax($entry, $config): void
    {
        $identifier = $config['identifier'];
        if ($num = $this->handleIntegers($entry)) {
            if (!isset($config['maxitems'])) {
                throw new Exception (
                    "'Folder' field '$identifier' configuration 'autoSizeMax' only takes effect when 'maxitems' is set to " .
                    "greater than 1, if set."
                );
            }
            if ($this->maxitems < 1) {
                $this->validateInteger($config['maxitems'], $config, 'maxitems', 'Folder', 1, PHP_INT_MAX);
                $this->maxitems = intval($config['maxitems']);
                if ($this->maxitems < 2) {
                    throw new Exception (
                        "'Folder' field '$identifier' configuration 'autoSizeMax' only takes effect when 'maxitems' is set to " .
                        "greater than 1, if set."
                    );
                }
            }
            $this->autoSizeMax = $num;
        } else {
            throw new Exception (
                "'Folder' field '$identifier' configuration 'autoSizeMax' must be of type integer or " .
                "a string that represents an integer number, if set."
            );
        }
    }

    private function _validateElementBrowserEntryPoints($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Group' field '$identifier' configuration 'elementBrowserEntryPoints' must be of type array, if set."
            );
        }
        $i = 0;
        foreach ($entry as $key => $value) {
            if (!is_string($key)) {
                throw new Exception (
                    "'Group' field '$identifier' configuration 'elementBrowserEntryPoints[$i][$key]' key must be of type string."
                );
            }

            if ($key === '_default' && (!is_string($value) && !is_int($value))) {
                throw new Exception (
                    "'Group' field '$identifier' configuration 'elementBrowserEntryPoints[$i][$key]' value must be of type string that ".
                    "represents an entry point if the key is set to '_default'. Fix: _default: '1:/styleguide/', " .
                    "_default: '###CURRENT_PID###', _default: '###PAGE_TSCONFIG_ID###', _default: '###SITEROOT###', ..."
                );
            }
            $i++;
        }
    }

    private function _validateConfig_userFunc(mixed $value, array $config, string $setting): void
    {
        $this->validateUserFunc($value, $config, $setting, 'Group');
    }

    private function _validateConfig_parameters(mixed $value, array $config, string $setting): void
    {
        $this->validateArrayStringString($value, $config, $setting, 'Group');
    }

    private function _validateFilter(mixed $entry, array $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Group' field '$identifier' configuration 'filter' must be of type array, if set.\n" .
                "Fix:\nfilter:\n  -\n    userFunc: '\Vendor\Extension\UserFunction\ClassName -> method'\n    parameters:\n" .
                "      key1: 'value1'\n      key2: value2"
            );
        }
        $i = 0;
        foreach ($entry as $value) {
            if (!is_array($value)) {
                throw new Exception (
                    "'Group' field '$identifier' configuration 'filter[$i]' must be of type array, if set.\n" .
                    "Fix:\nfilter:\n  -\n    userFunc: '\Vendor\Extension\UserFunction\ClassName -> method'\n    parameters:\n" .
                "      key1: 'value1'\n      key2: value2"
                );
            }
            $j = 0;
            foreach ($value as $key => $filter) {
                if (!key_exists($key, self::FILTER_TYPES)) {
                    throw new Exception (
                        "'Group' field '$identifier' configuration 'filter[$i][$j]' key '$key' is no valid keyword.\n" .
                        "Valid keywords are: " . implode(',', array_keys(self::FILTER_TYPES))
                    );
                }
                switch (self::FILTER_TYPES[$key]) {
                    case parent::FUNCTION:
                        $function = "_validateConfig_" . $key;
                        call_user_func([$this, $function], $filter, $config, "filter[$i][$key]");
                        break;
                }
                $j++;
            }
            $i++;
        }
    }

    private function _validateMmMatchFields(mixed $value, array $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($value)) {
            throw new Exception (
                "'Group' field '$identifier' configuration 'MM_match_fields' must be of type array, if set."
            );
        }
        
        try {
            $this->validateArrayStringString($value, $config, 'MM_match_fields', 'Group');
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage() . "Fix:\nMM_match_fields:\n  fieldName1: 'fieldValue1'\n  fieldName2: 'fieldValue2'");
        }

        $i = 0;
        foreach ($value as $field => $unused) {
            $this->validateField($field, $config, "MM_match_fields[$i]", 'Group');
            $i++;
        }
    }

    private function _validateMmOppositeUsage(mixed $value, array $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($value)) {
            throw new Exception (
                "'Group' field '$identifier' configuration 'MM_oppositeUsage' must be of type array, if set." .
                "Fix:\nMM_oppositeUsage:\n  - fieldName1: 'value1'\n  - fieldName2: 'value2'"
            );
        }

        
        foreach ($value as $table => $fields) {
            $this->validateTable($table, $config, "MM_oppositeUsage['$table']", 'Group');
            if (!is_array($fields)) {
                throw new Exception (
                    "'Group' field '$identifier' configuration 'MM_oppositeUsage['$table']' value must be of type array, if set."
                );
            }
            $i = 0;
            foreach ($fields as $field) {
                $this->validateField($field, $config, "MM_oppositeUsage['$table'][$i]", 'Group', [$table]);
                $i++;
            }
        }
    }

    private function _validateAndSetSuggestOptions(mixed $value, array $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($value)) {
            throw new Exception (
                "'Group' field '$identifier' configuration 'suggestOptions' must be of type array, if set." .
                "Fix:\suggestOptions:\n  default:\n    additionalSearchFields: '...'\n    addWhere: '...'"
            );
        }
        $i = 0;
        foreach ($value as $key => $setting) {
            if (!is_string($key)) {
                throw new Exception (
                    "'Group' field '$identifier' configuration 'suggestOptions[$i]' key be of type string, if set." .
                    "Fix:\suggestOptions:\n  default:\n    additionalSearchFields: '...'\n    addWhere: '...'"
                );
            }
            if (!is_array($setting)) {
                throw new Exception (
                    "'Group' field '$identifier' configuration 'suggestOptions[$key]' must be of type array, if set." .
                    "Fix:\suggestOptions:\n  default:\n    additionalSearchFields: '...'\n    addWhere: '...'"
                );
            }
            $this->suggestOptions[$key] = new GroupFieldSuggestOptionsConfig();
            $this->suggestOptions[$key]->arrayToConfig($setting, $config, $key);
        }
    }

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    switch ($configKey) {
                        case 'allowed':
                            $this->_validateTableList($value, $config, 'allowed');
                            $this->allowed = $value;
                            break;
                        case 'autoSizeMax':
                            $this->_validateAutoSizeMax($value, $config);
                            $this->autoSizeMax = intval($value);
                            break;
                        case 'elementBrowserEntryPoints':
                            $this->_validateElementBrowserEntryPoints($value, $config);
                            $this->elementBrowserEntryPoints = $value;
                            break;
                        case 'filter':
                            $this->_validateFilter($value, $config);
                            $this->filter = $value;
                            break;
                        case 'foreign_table':
                            $this->validateTable($value, $config, 'foreign_table', 'Group');
                            $this->foreign_table = $value;
                            break;
                        case 'maxitems':
                            $this->validateInteger($value, $config, 'maxitems', 'Group', 1, PHP_INT_MAX, true, true, 'minitems');
                            $this->maxitems = intval($value);
                            break;
                        case 'minitems':
                            $this->validateInteger($value, $config, 'maxitems', 'Group', 1, PHP_INT_MAX, true, false, 'maxitems');
                            $this->minitems = intval($value);
                            break;
                        case 'MM':
                            $this->validateTable($value, $config, 'MM', 'Group');
                            $this->MM = $value;
                            break;
                        case 'MM_match_fields':
                            $this->_validateMmMatchFields($value, $config);
                            $this->MM_match_fields = $value;
                            break;
                        case 'MM_opposite_field':
                            $this->validateField($value, $config, 'MM_opposite_field', 'Group');
                            $this->MM_opposite_field = $value;
                            break;
                        case 'MM_oppositeUsage':
                            $this->_validateMmOppositeUsage($value, $config);
                            $this->MM_oppositeUsage = $value;
                            break;
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'Group', 1, PHP_INT_MAX, true, false, 'autoSizeMax');
                            $this->size = intval($value);
                            break;
                        case 'suggestOptions':
                            $this->_validateAndSetSuggestOptions($value, $config);
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

final class GroupField extends Fields
{
    protected GroupFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('link', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new GroupFieldConfig();
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