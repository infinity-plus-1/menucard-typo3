<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use Exception;

final class SelectFieldFileFolderConfig extends Config
{
    protected string $allowedExtensions = '';
    protected int $depth = -1;
    protected string $folder = '';
}

final class SelectFieldItem
{
    protected string $label = '';
    protected string|int $value = '';
    protected string $icon = '';
    protected string $group = '';
    protected string|array $description = '';
    
    public function __construct(array $item)
    {
        $properties = get_object_vars($this);
        foreach ($item as $configKey => $config) {
            if (array_key_exists($configKey, $properties)) {
                $this->$configKey = $config;
            }
        }
    }
}

final class SelectFieldConfig extends Config
{
    protected bool $allowNonIdValues = false;
    protected string $authMode = '';
    protected int $autoSizeMax = -1;
    protected int $dbFieldLength = -1;
    protected string $default = '';
    protected bool $disableNoMatchingValueElement = false;
    protected ?SelectFieldFileFolderConfig $fileFolderConfig = NULL;
    protected string $foreign_table = '';
    protected string $foreign_table_item_group = '';
    protected string $foreign_table_prefix = '';
    protected string $foreign_table_where = '';
    protected array $itemGroups = [];
    protected array $items = [];
    protected string $itemsProcFunc = '';
    protected int $maxitems = -1;
    protected int $minitems = -1;
    protected string $MM = '';
    protected array $MM_match_fields = [];
    protected string $MM_opposite_field = '';
    protected array $MM_oppositeUsage = [];
    protected string $MM_table_where = '';
    protected bool $multiple = false;
    protected bool $readOnly = false;
    protected int $size = 1;
    protected array $sortItems = [];

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                switch ($configKey) {
                    case 'authMode':
                        if ('explicitAllow' === $value) $this->authMode = 'explicitAllow';
                        break;
                    case 'items':
                        foreach ($value as $item) {
                            $this->items[] = new SelectFieldItem($item);
                        }
                        break;
                    case 'sortItems':
                        foreach ($value as $sortBy => $order) {
                            if ($sortBy === 'label' || $sortBy === 'value') {
                                if ($order === 'asc' || $order === 'desc') {
                                    $this->sortItems[$sortBy] = $order; 
                                }
                            } else if (str_contains($order, '->')) {
                                $this->sortItems[$sortBy] = $order; 
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

    public function configToElement(): array
    {
        $properties = get_object_vars($this);
        return parent::_configToElement('input', $properties);
    }
}

final class SelectField extends Fields
{
    protected SelectFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $config = new SelectFieldConfig();
        $config->arrayToConfig($field);
        $this->config = $config;
        $this->__arrayToField('select', $field);
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