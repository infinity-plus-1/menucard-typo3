<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder;

use DS\CbBuilder\FieldBuilder\Fields\Fields;
use DS\CbBuilder\FieldBuilder\Fields\PaletteContainer;
use DS\CbBuilder\FieldBuilder\Fields\TextField;
use DS\CbBuilder\Fields\Field;
use Exception;
use Symfony\Component\Yaml\Yaml;

class FieldBuilderException extends Exception {}

final class FieldBuilder
{
    protected array $fields = [];
    protected ?PaletteContainer $currentPalette = NULL;

    public function __construct
    (
        protected string $path,
        protected string $identifier
    ){}
        
    private function _readFields(): array
    {
        $identifier = $this->identifier;
        $fields = Yaml::parseFile($this->path . "/ContentBlocks/$identifier/fields.yaml");
        if (is_array($fields)) {
            if (array_key_exists('fields', $fields)) {
                return $fields['fields'];
            }
        }
    }

    public function buildFields(): void
    {
        $fieldsArray = $this->_readFields();
        $isFirst = true;
        foreach ($fieldsArray as $field) {
            if (array_key_exists('type', $field)) {
                if ($field['type'] === 'Linebreak' && $this->currentPalette !== NULL) {
                    $this->currentPalette->addToPalette('--linebreak--');
                } else {
                    $fieldObj = Fields::createField($field, 'tt_content');
                    if ($isFirst) {
                        if ($fieldObj->getType() !== 'Palette') {
                            $this->fields[0] = new PaletteContainer([]);
                        }
                        $this->currentPalette = $this->fields[0];
                        $this->currentPalette->addToPalette($fieldObj->getIdentifier(), true);
                        $isFirst = false;
                    } else {
                        $this->currentPalette->addToPalette($fieldObj->getIdentifier());
                    }
                    $this->fields[] = $fieldObj;
                }
            }
            
        } 
        dump($this->fields[1]);
    }
}