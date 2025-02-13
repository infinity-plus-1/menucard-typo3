<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use Exception;

final class TextareaFieldConfig extends Config
{
    private bool $autocomplete = false;
    private string $default = '';
    private string $eval = '';
    private string $is_in = '';
    private int $max = -1;
    private int $min = -1;
    private string $mode = '';
    private bool $nullable = false;
    private string $placeholder = '';
    private bool $readOnly = false;
    private bool $required = false;
    private array $search = [];
    private int $size = -1;
    private string $softref = '';
    private array $valuePicker = [];

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey)) {
                $this->$configKey = $value;
            }
        }
    }

    public function configToElement(): array
    {
        $properties = get_object_vars($this);
        return parent::_configToElement('input', $properties);
    }
}

final class TextareaField extends Fields
{
    protected TextFieldConfig $config;
    protected int $cols = -1;
    protected int $rows = -1;
    protected bool $enableRichtext = false;
    protected string $renderType = '';
    protected string $default = '';

    private function _arrayToField(array $field): void
    {
        $config = new TextFieldConfig();
        $config->arrayToConfig($field);
        $this->config = $config;
        $this->__arrayToField('text', $field);
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