<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;

final class FolderFieldConfig extends Config
{
    protected int $autoSizeMax = -1;
    protected array $elementBrowserEntryPoints = [];
    protected bool $hideDeleteIcon = false;
    protected bool $hideMoveIcons = false;
    protected int $maxitems = -1;
    protected int $minitems = -1;
    protected bool $multiple = false;
    protected bool $readOnly = false;
    protected int $size = -1;


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
                "'Folder' field '$identifier' configuration 'elementBrowserEntryPoints' must be of type array, if set."
            );
        }
        $i = 0;
        foreach ($entry as $key => $value) {
            if (!is_string($key)) {
                throw new Exception (
                    "'Folder' field '$identifier' configuration 'elementBrowserEntryPoints[$i][$key]' key must be of type string."
                );
            }

            if ($key === '_default' && (!is_string($value) && !is_int($value))) {
                throw new Exception (
                    "'Folder' field '$identifier' configuration 'elementBrowserEntryPoints[$i][$key]' value must be of type string that ".
                    "represents an entry point if the key is set to '_default'. Fix: _default: '1:/styleguide/', " .
                    "_default: '###CURRENT_PID###', _default: '###PAGE_TSCONFIG_ID###', _default: '###SITEROOT###', ..."
                );
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
                        case 'autoSizeMax':
                            $this->_validateAutoSizeMax($value, $config);
                            $this->autoSizeMax = $value;
                            break;
                        case 'elementBrowserEntryPoints':
                            $this->_validateElementBrowserEntryPoints($value, $config);
                            $this->elementBrowserEntryPoints = $value;
                            break;
                        case 'maxitems':
                            $this->validateInteger($value, $config, 'maxitems', 'Folder', 1, PHP_INT_MAX);
                            $this->maxitems = $value;
                            break;
                        case 'minitems':
                            $this->validateInteger($value, $config, 'minitems', 'Folder', 1, PHP_INT_MAX);
                            $this->minitems = $value;
                            break;
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'Folder', 1, PHP_INT_MAX);
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
        return parent::_configToElement('folder', $properties);
    }
}

final class FolderField extends Fields
{
    protected FolderFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('folder', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new FolderFieldConfig();
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