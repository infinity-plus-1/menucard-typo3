<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder;

use DS\CbBuilder\FieldBuilder\Fields\Fields;
use DS\CbBuilder\FieldBuilder\Fields\PaletteContainer;
use DS\CbBuilder\FieldBuilder\Fields\TextField;
use DS\CbBuilder\Fields\Field;
use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use Exception;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    public static function isSurpressedWarning(int|string $code): bool
    {

        if (
            isset($GLOBALS['CbBuilder']['config']['surpressedWarnings'])
            && $GLOBALS['CbBuilder']['config']['surpressedWarnings'] !== NULL
            && !empty($GLOBALS['CbBuilder']['config']['surpressedWarnings'])
        ) {
            $surpressedWarnings = $GLOBALS['CbBuilder']['config']['surpressedWarnings'];
            $surpressedWarnings = is_int($surpressedWarnings) ? strval($surpressedWarnings) : $surpressedWarnings;
            $surpressedWarnings = GeneralUtility::trimExplode(',', $surpressedWarnings);
            $code = strval($code);
            return in_array($code, $surpressedWarnings);
        } else {
            return false;
        }
    }

    private static function _searchFields(array $fields, string $field, array $types, array $excludes)
        {
            foreach ($fields as $_field) {
                foreach ($_field as $key => $value) {
                    switch ($key) {
                        case 'identifier':
                            if ($value === $field) {
                                if ((empty($types) || in_array($_field['type'], $types)) && !in_array($_field['type'], $excludes)) {
                                    return true;
                                }
                            }
                            break;
                        case 'type':
                            if ('Collection' === $value) {
                                if (isset($_field['fields']) && FieldBuilder::_searchFields($_field['fields'], $field, $types, $excludes)) {
                                    return true;
                                }
                            }
                        default:
                            break;
                    }
                }
            }
            return false;
        }

    public static function fieldExists(string $field, ?string $types = '', ?string $excludes = ''): bool
    {
        $types = $types !== '' ? GeneralUtility::trimExplode(',', $types) : [];
        $excludes = $excludes !== '' ? GeneralUtility::trimExplode(',', $excludes) : [];
        $fields = $GLOBALS['CbBuilder']['fields'];
        if (FieldBuilder::_searchFields($fields, $field, $types, $excludes)) {
            return true;
        }

        return false;
    }
        
    private function _readFields(): array
    {
        $identifier = $this->identifier;
        $fields = Yaml::parseFile($this->path . "/ContentBlocks/$identifier/fields.yaml");
        if (is_array($fields)) {
            if (array_key_exists('fields', $fields)) {
                $GLOBALS['CbBuilder']['fields'] = $fields['fields'];
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
        dump($this->fields[3]);
    }
}