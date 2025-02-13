<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use Exception;

final class CollectionContainer extends Fields
{
    protected string $identifier = '';
    protected bool $isHiddenPalette = false;
    protected string $showitem = '';

    public function addToPalette(string $identifier, ?bool $trim = false) {
        if (!$trim) $this->showitem .= ", $identifier";
        else $this->showitem .= $identifier;
    }

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('palette', $field);
    }

    public function __construct(array $field)
    {
        $this->_arrayToField($field);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}