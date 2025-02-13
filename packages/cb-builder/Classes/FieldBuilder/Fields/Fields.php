<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use Exception;

class FieldException extends Exception {}

class Fields
{
    /** @var string $type The type of the field. Always starting with an uppercase letter. */
    protected string $type = '';
    /** @var string $identifier Use an unique identifier for that field. This will be the column name in the db. */
    protected string $identifier = '';
    /** 
     * @var bool $useExistingField You can reuse existing columns of the tt_content table.
     *  If there is no existing column in tt_content an error will be thrown if Strict is set to true
     *  or this variable is treated as false else.
     */
    protected bool $useExistingField = false;
    /** @var string $table The table the field belongs to. Is tt_content by default or the identifier of the current 'Collection' */
    protected string $table = '';
    /** @var string $label Define a label that functions as a short identifier for BE users. */
    protected string $label = '';
    /** @var string $description A short description for that element. */
    protected string $description = '';
    protected string $l10nMode = '';

    public static function createField(array $field, string $table)
    {
        if (array_key_exists('type', $field)) {
            switch ($field['type']) {
                case 'Text':
                    return new TextField($field, $table);
                    break;
                case 'Textarea':
                    return new TextareaField($field, $table);
                    break;
                case 'File':
                    return new FileField($field, $table);
                    break;
                case 'Select':
                    return new SelectField($field, $table);
                    break;
                case 'Number':
                    return new NumberField($field, $table);
                    break;
                case 'Checkbox':
                    return new CheckboxField($field, $table);
                    break;

                case 'Collection':
                    return new CollectionContainer($field, $table);
                    break;
                default:
                    # code...
                    break;
            }
        }
    }

    protected function __arrayToField(string $type, array $fields): void
    {
        $this->type = $type;
        $properties = get_object_vars($this);
        foreach ($fields as $key => $value) {
            if (array_key_exists($key, $properties)) {
                $this->$key = $value;
            }
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getL10nMode(): string
    {
        return $this->l10nMode;
    }
}