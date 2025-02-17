<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use Exception;

final class FileFieldAppearanceConfig extends Config
{
    protected bool $collapseAll = true;
    protected bool $expandSingle = false;
    protected string $createNewRelationLinkTitle = '';
    protected string $addMediaLinkTitle = '';
    protected string $uploadFilesLinkTitle = '';
    protected bool $useSortable = false;
    protected bool $showPossibleLocalizationRecords = false;
    protected bool $showAllLocalizationLink = false;
    protected bool $showSynchronizationLink = false;
    protected array $enabledControls = [];
    protected array $headerThumbnail = [];
    protected bool $fileUploadAllowed = true;
    protected bool $fileByUrlAllowed = true;
    protected bool $elementBrowserEnabled = true;

    const ENABLED_CONTROLS = [
        'info', 'new', 'dragdrop', 'sort', 'hide', 'delete', 'localize'
    ];

    const HEADER_THUMBNAILS = [
        'field', 'width', 'height'
    ];

    private $headerThumbnailFunctions;

    public function __construct()
    {
        $this->headerThumbnailFunctions = [
            'field' => function ($value, $identifier) {
                if (!is_string($value)) {
                    throw new Exception(
                        "'File' field '$identifier' configuration 'appearance['headerThumbnail']['field']' value must be of type string, if set."
                    );
                }
            },
            'width' => function ($value, $identifier, $config) {
                self::_validateThumbnailWidthOrHeight($value, $identifier, $config);
            },
            'height' => function ($value, $identifier, $config) {
                self::_validateThumbnailWidthOrHeight($value, $identifier, $config);
            }
        ];
    }

    private function _validateThumbnailWidthOrHeight($value, $identifier, $config): void
    {
        if (!is_string($value) && !is_int($value)) {
            throw new Exception (
                "'File' field '$identifier' configuration 'appearance['headerThumbnail']['$config']' value must be either of type string " .
                "or type integer, if set."
            );
        }
        if (is_string($value)) {
            $match = [];
            preg_match("/\\d*c?/", $value, $match);
            if ($match[0] !== $value) {
                throw new Exception (
                    "'File' field '$identifier' configuration 'appearance['headerThumbnail']['$config']' value must be an integer or " .
                    "a string that represents an integer. A 'c' can be appended if the element shall be cropped. Fix: " .
                    "appearance['headerThumbnail']['$config'] => '100c'"
                );
            }
        }
    }

    private function _validateIsElementBrowserEnabled($entry, $config, string $_config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'File' field '$identifier' configuration '$_config' must be of type string, if set."
            );
        }

        if (isset($config['appearance']['elementBrowserEnabled']) && $config['appearance']['elementBrowserEnabled'] === false) {
            throw new Exception (
                "'File' field '$identifier' configuration '$_config' takes only effect if " .
                "['config']['appearance']['elementBrowserEnabled'] is true. Fix: Set " .
                "['config']['appearance']['elementBrowserEnabled'] to true."
            );
        }
    }

    private function _validateEnabledControls($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'File' field '$identifier' configuration 'enabledControls' must be of type array, if set."
            );
        }

        $i = 0;
        foreach ($entry as $key => $value) {
            if (!in_array($key, self::ENABLED_CONTROLS)) {
                throw new Exception (
                    "'File' field '$identifier' configuration 'appearance['enabledControls'][$i]' key must be one " .
                    "of the following keywords: " . implode(', ', self::ENABLED_CONTROLS)
                );
            }
            if (!is_bool($value)) {
                throw new Exception (
                    "'File' field '$identifier' configuration 'appearance['enabledControls'][$i]' value must be of type boolean."
                );
            }
            $i++;
        }
    }

    private function _validateHeaderThumbnail($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'File' field '$identifier' configuration 'headerThumbnail' must be of type array, if set."
            );
        }

        foreach ($entry as $key => $value) {
            if (!in_array($key, FileFieldAppearanceConfig::HEADER_THUMBNAILS)) {
                throw new Exception (
                    "'File' field '$identifier' configuration 'appearance['headerThumbnail'][$key]' key must be one " .
                    "of the following keywords: " . implode(', ', self::HEADER_THUMBNAILS)
                );
            }
            call_user_func($this->headerThumbnailFunctions[$key], $value, $identifier, $key);
        }
    }

    public function arrayToConfig(array $config, array $_config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'enabledControls':
                        $this->_validateEnabledControls($value, $_config);
                        $this->enabledControls = $value;
                        break;
                    case 'headerThumbnail':
                        $this->_validateHeaderThumbnail($value, $_config);
                        $this->headerThumbnail = $value;
                        break;
                    case 'createNewRelationLinkTitle':
                        $this->_validateIsElementBrowserEnabled($value, $_config, 'createNewRelationLinkTitle');
                        $this->createNewRelationLinkTitle = $value;
                        break;
                    case 'addMediaLinkTitle':
                        $this->_validateIsElementBrowserEnabled($value, $_config, 'addMediaLinkTitle');
                        $this->addMediaLinkTitle = $value;
                        break;
                    case 'uploadFilesLinkTitle':
                        $this->_validateIsElementBrowserEnabled($value, $_config, 'addMediaLinkTitle');
                        $this->uploadFilesLinkTitle = $value;
                        break;
                    default:
                        $this->$configKey = $value;
                        break;
                }
            }
        }
    }
}

final class FileFieldConfig extends Config
{
    protected array $allowed = [];
    protected ?FileFieldAppearanceConfig $appearance = NULL;
    protected array $disallowed = [];
    protected int $maxitems = -1;
    protected int $minitems = 0;
    protected array $overrideChildTca = [];
    protected bool $readOnly = false;

    private function _validateAllowedOrDisallowed($entry, $config, string $_config): void
    {
        $identifier = $config['identifier'];
        if (!is_array($entry)) {
            throw new Exception (
                "'File' field '$identifier' configuration '$_config' value must be of type array, if set."
            );
        }

        $i = 0;
        foreach ($entry as $key => $value) {
            if (!is_string($key)) {
                throw new Exception (
                    "'File' field '$identifier' configuration '$_config[$i]' key must be of type string, if set."
                );
            }
            if (!is_string($value)) {
                throw new Exception (
                    "'File' field '$identifier' configuration '$_config[$i]' value must be of type string, if set."
                );
            }
            $i++;
        }
    }

    private function _validateMinOrMaxItems($entry, $config, $_config)
    {
        $identifier = $config['identifier'];
        if (!is_int($entry)) {
            throw new Exception (
                "'File' field '$identifier' configuration '$_config' value must be of type integer, if set."
            );
        }

        if ($entry <= 0) {
            throw new Exception (
                "'File' field '$identifier' configuration '$_config' value must be greater than zero, if set."
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
                        case 'appearance':
                            $this->appearance = new FileFieldAppearanceConfig();
                            $this->appearance->arrayToConfig($value, $config);
                            break;
                        case 'disallowed':
                            $this->_validateAllowedOrDisallowed($value, $config, 'disallowed');
                            $this->disallowed = $value;
                            break;
                        case 'allowed':
                            $this->_validateAllowedOrDisallowed($value, $config, 'allowed');
                            $this->allowed = $value;
                            break;
                        case 'maxitems':
                            $this->_validateMinOrMaxItems($value, $config, 'maxitems');
                            $this->maxitems = $value;
                            break;
                        case 'minitems':
                            $this->_validateMinOrMaxItems($value, $config, 'minitems');
                            $this->minitems = $value;
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
        return parent::_configToElement('file', $properties);
    }
}

final class FileField extends Fields
{
    protected FileFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('file', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new FileFieldConfig();
        $config->arrayToConfig($field);
        $this->config = $config;
    }

    public function fieldToElement(): array
    {
        $element = [];
        $element['config'] = $this->config->configToElement();
        return $element;
    }

    public function __construct(array $field)
    {
        $this->_arrayToField($field);
    }
}