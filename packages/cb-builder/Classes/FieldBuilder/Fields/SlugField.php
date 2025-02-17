<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\CbBuilder\FieldBuilder\FieldBuilder;
use Exception;

final class SlugFieldGeneratorOptionsConfig extends Config
{
    protected array $fields = [];
    protected string $fieldSeparator = '';
    protected ?bool $prefixParentPageSlug = NULL;
    protected array $replacements = [];
    protected array $postModifiers = [];

    private function _validateFieldsArrays(array $fields, array $config, int $i): void
    {
        $identifier = $config['identifier'];
        $j = 0;
        foreach ($fields as $field) {
            if (is_array($field)) {
                $this->_validateFieldsArrays($field, $config, $j);
            } else if (!is_string($field)) {
                throw new Exception (
                    "'Slug' field '$identifier' configuration 'generatorOptions['fields'][$i][$j]' must be of type string, if set.\n" .
                    "Fix:\ngeneratorOptions:\n  fields:\n    - 'input1'\n    - 'input2'\n    -\n" .
                    "      - 'input3'\n      - 'input4'"
                );
            } else {
                if (!FieldBuilder::isSurpressedWarning(862169179) && !FieldBuilder::fieldExists($field, 'Text')) {
                    throw new Exception (
                        "WARNING: 'Slug' field '$identifier' configuration 'generatorOptions['fields'][$i][$j]' field '$field' " .
                        "does not exist in scope. You can surpress this warning if it exists somewhere else.\n" .
                         "You can surpress this warning in the cbconfig.yaml by adding the code 862169179 to surpressWarning."
                    );
                }
            }
            $j++;
        }
    }

    private function _validateFields($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'generatorOptions['fields']' must be of type array, if set.\n" .
                "Fix:\ngeneratorOptions:\n  fields:\n    - 'input1'\n    - 'input2'\n    -\n" .
                    "      - 'input3'\n      - 'input4'"
            );
        }
        $this->_validateFieldsArrays($entry, $config, 0);
    }

    private function _validateFieldSeparator($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'generatorOptions['fieldSeparator']' must be of type string, if set."
            );
        }

        if (!FieldBuilder::isSurpressedWarning(862169180)) {
            if (preg_match('/[^a-zA-Z0-9\-._~\/]/', $entry)) {
                throw new Exception (
                    "WARNING: 'Slug' field '$identifier' configuration 'generatorOptions['fieldSeparator']' may needs to be encoded.\n" .
                    "You can surpress this warning in the cbconfig.yaml by adding the code 862169180 to surpressWarning."
                );
            }
        }

        if (!FieldBuilder::isSurpressedWarning(862169181)) {
            if (strlen($entry) > 1) {
                throw new Exception (
                    "WARNING: 'Slug' field '$identifier' configuration 'generatorOptions['fieldSeparator']' is longer " .
                    "than one char, is this intended?\n" .
                    "You can surpress this warning in the cbconfig.yaml by adding the code 862169181 to surpressWarning."
                );
            }
        }
    }

    private function _validateReplacements($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'generatorOptions['replacements']' must be of type array, if set.\n" .
                "Fix:\ngeneratorOptions:\n  replacements:\n    replaceThis: 'withThis'\n    andThis: 'withSomethingElse'"
            );
        }
        $i = 0;
        foreach ($entry as $key => $value) {
            if (!is_string($key)) {
                throw new Exception (
                    "'Slug' field '$identifier' configuration 'generatorOptions['replacements'][$i]' needle (key) must be of " .
                    "type string, if set.\n" .
                    "Fix:\ngeneratorOptions:\n  replacements:\n    replaceThis: 'withThis'\n    andThis: 'withSomethingElse'"
                );
            }
            if (!is_string($value)) {
                throw new Exception (
                    "'Slug' field '$identifier' configuration 'generatorOptions['replacements'][$i]' replacement (value) must be of " .
                    "type string, if set.\n" .
                    "Fix:\ngeneratorOptions:\n  replacements:\n    replaceThis: 'withThis'\n    andThis: 'withSomethingElse'"
                );
            }
            $i++;
        }
    }

    private function _validatePostModifiers($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'generatorOptions['postModifiers']' must be of type array, if set.\n" .
                "Fix:\ngeneratorOptions:\n  postModifiers:\n    - '\Vendor\Extension\UserFunction\ClassName -> method'\n" .
                "    - '\Vendor\Extension\UserFunction\ClassName2 -> method2'\n    ..."
            );
        }
        $i = 0;
        foreach ($entry as $value) {
            $this->validateUserFunc($value, $config, "generatorOptions['postModifiers'][$i]", 'Slug');
            $i++;
        }
    }

    public function arrayToConfig(array $config, array $fieldConfig): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'fields':
                        $this->_validateFields($value, $fieldConfig);
                        $this->fields = $value;
                        break;
                    case 'fieldSeparator':
                        $this->_validateFieldSeparator($value, $fieldConfig);
                        $this->fieldSeparator = $value;
                        break;
                    case 'replacements':
                        $this->_validateReplacements($value, $fieldConfig);
                        $this->replacements = $value;
                        break;
                    case 'postModifiers':
                        $this->_validatePostModifiers($value, $fieldConfig);
                        $this->postModifiers = $value;
                        break;
                    default:
                        $this->$configKey = $value;
                        break;
                }
            }
        }
    }
}

final class SlugFieldConfig extends Config
{
    protected array $appearance = [];
    protected string $eval = '';
    protected string $fallbackCharacter = '';
    protected ?SlugFieldGeneratorOptionsConfig $generatorOptions = NULL;
    protected ?bool $prependSlash = NULL;

    const EVAL_KEYWORDS = [
        'unique' => parent::STRING_TYPE,
        'uniqueInSite' => parent::STRING_TYPE,
        'uniqueInPid' => parent::STRING_TYPE
    ];

    const APPEARANCE_KEYWORDS = [
        'prefix' => parent::STRING_TYPE
    ];

    private function _validateAppearance($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'appearance' must be of type array, if set.\n" .
                "Fix:\nappearance:\n  prefix: '\Vendor\Extension\UserFunction\ClassName -> method'"
            );
        }
        foreach ($entry as $key => $value) {
            switch ($key) {
                case 'prefix':
                    $this->validateUserFunc($value, $config, "appearance['prefix']", 'Slug');
                    break;
                default:
                    throw new Exception (
                        "'Slug' field '$identifier' configuration 'appearance['$key']' $key is not a valid keyword, if set. " .
                        "Valid keywords are: " . implode(', ', array_keys(self::APPEARANCE_KEYWORDS)) . "\n" .
                        "Fix:\nappearance:\n  prefix: '\Vendor\Extension\UserFunction\ClassName -> method'"
                    );
                    break;
            }
        }
    }

    private function _validateFallbackCharacter($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'fallbackCharacter' must be of type string, if set."
            );
        }

        if (!FieldBuilder::isSurpressedWarning(862169177)) {
            if (preg_match('/[^a-zA-Z0-9\-._~\/]/', $entry)) {
                throw new Exception (
                    "WARNING: 'Slug' field '$identifier' configuration 'fallbackCharacter' may needs to be encoded.\n" .
                    "You can surpress this warning in the cbconfig.yaml by adding the code 862169177 to surpressWarning."
                );
            }
        }

        if (!FieldBuilder::isSurpressedWarning(862169178)) {
            if (strlen($entry) > 1) {
                throw new Exception (
                    "WARNING: 'Slug' field '$identifier' configuration 'fallbackCharacter' is longer than one char, is this intended?\n" .
                    "You can surpress this warning in the cbconfig.yaml by adding the code 862169178 to surpressWarning."
                );
            }
        }
    }

    private function _validateEval($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'eval' must be of type string, if set."
            );
        }
        if (!in_array($entry, array_keys(self::EVAL_KEYWORDS))) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'eval' $entry is not a valid keyword, if set. " .
                "Valid keywords are: " . implode(', ', array_keys(self::EVAL_KEYWORDS))
            );
        }
    }

    private function _validategeneratorOptions($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'Slug' field '$identifier' configuration 'generatorOptions' must be of type array, if set."
            );
        }
        
        $this->generatorOptions = new SlugFieldGeneratorOptionsConfig();
        $this->generatorOptions->arrayToConfig($entry, $config);
    }

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    switch ($configKey) {
                        case 'appearance':
                            $this->_validateAppearance($value, $config);
                            $this->appearance = $value;
                            break;
                        case 'eval':
                            $this->_validateEval($value, $config);
                            $this->eval = $value;
                            break;
                        case 'fallbackCharacter':
                            $this->_validateFallbackCharacter($value, $config);
                            $this->fallbackCharacter = $value;
                            break;
                        case 'generatorOptions':
                            $this->generatorOptions = new SlugFieldGeneratorOptionsConfig();
                            $this->generatorOptions->arrayToConfig($value, $config);
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
        return parent::_configToElement('slug', $properties);
    }
}

final class SlugField extends Fields
{
    protected SlugFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('slug', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new SlugFieldConfig();
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