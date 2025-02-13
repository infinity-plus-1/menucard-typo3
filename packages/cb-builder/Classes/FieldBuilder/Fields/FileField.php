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

    public function arrayToConfig(array $config): void
    {
        $enabledControlsAllowed = [
            'info', 'new', 'dragdrop', 'sort', 'hide', 'delete', 'localize'
        ];
        $headerThumbnailAllowed = [
            'field', 'width', 'height'
        ];

        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'enabledControls':
                        foreach ($value as $controlsKey => $controlsSetting) {
                            if (in_array($controlsKey, $enabledControlsAllowed) && is_bool($controlsSetting)) {
                                $this->enabledControls[$controlsKey] = $controlsSetting;
                            }
                        }
                        break;
                    case 'headerThumbnail':
                        foreach ($value as $thumbnailKey => $thumbnailSetting) {
                            if (array_key_exists('field', $value)) {
                                if (in_array($thumbnailKey, $headerThumbnailAllowed) && is_string($thumbnailSetting)) {
                                    $this->headerThumbnail[$thumbnailKey] = $thumbnailSetting;
                                }
                            }
                        }
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
    private array $allowed = [];
    private ?FileFieldAppearanceConfig $appearance = NULL;
    private array $disallowed = [];
    private int $maxitems = -1;
    private int $minitems = 0;
    private array $overrideChildTca = [];
    private bool $readOnly = false;


    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                if ($configKey === 'appearance') {
                    $this->appearance = new FileFieldAppearanceConfig();
                    $this->appearance->arrayToConfig($value);
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
        $config = new FileFieldConfig();
        $config->arrayToConfig($field);
        $this->config = $config;
        $this->__arrayToField('file', $field);
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