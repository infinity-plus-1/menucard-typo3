<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\Utility;
use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class NoneFieldConfig extends Config
{
    protected string $default = '';
    protected string $format = '';
    protected array $format_ = [];
    protected int $size = -1;

    const FORMAT_KEYWORDS = [
        'date' => [
            'option' => parent::STRING_TYPE,
            'appendAge' => parent::BOOL_TYPE
        ],
        'datetime',
        'time',
        'timesec',
        'year',
        'int' => [
            'base' => [
                'dec', 'hex', 'HEX', 'oct', 'bin'
            ]
        ],
        'float' => [ 'precision' => parent::FUNCTION ],
        'number' => [ 'option' => parent::STRING_TYPE ],
        'md5',
        'filesize' => [ 'appendByteSize' => parent::BOOL_TYPE ],
        'user' => [ 'userFunc' => parent::FUNCTION ]
    ];
    
    private function _validateFormatOption_precision($value, $identifier): void
    {
        if (!$value = $this->handleIntegers($value)) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format['float']['precision']' must be of type integer or a string representing an " .
                "integer, if set."
            );
        }
        
        if ($value < 0 || $value > 10) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format['float']['precision']' must be a range between 0 and 10, if set."
            );
        }
    }

    private function _validateFormatOption_userFunc($value, $identifier): void
    {
        if (!is_string($value)) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format['user']['userFunc']' must be of type string, if set."
            );
        }
        
        if (!str_contains($value, '->')) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format['user']['userFunc']' must be in format " .
                "\Vendor\Extension\UserFunction\ClassName -> method, if set."
            );
        }

        
        if ($GLOBALS['CbBuilder']['config']['propertySpecific']['None']['format.']['user']['testIfClassAndMethodExists'] === true) {
            $className = $methodName = '';
            try {
                $splitted = explode('->', $value);
                $className = trim($splitted[0]);
                $methodName = trim($splitted[1]);
                GeneralUtility::makeInstance($className);
            } catch (\Throwable $th) {
                if (str_contains($th->getMessage(), 'not found')) {
                    throw new Exception (
                        "'None' field '$identifier' configuration 'format['user']['userFunc']' class $className not found."
                    );
                }
            }
            
            if (!method_exists($className, $methodName)) {
                throw new Exception (
                    "'None' field '$identifier' configuration 'format['user']['userFunc']' method $methodName not found."
                );
            }
        }
    }

    private function _validateFormat($entry, $config): void
    {
        $identifier = $config['identifier'];
        if (!is_string($entry)) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format' must be of type string, if set."
            );
        }

        if (!in_array($entry, self::FORMAT_KEYWORDS) && !array_key_exists($entry, self::FORMAT_KEYWORDS)) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format' must be one of the following keywords, if set: " .
                implode(', ', self::FORMAT_KEYWORDS)
            );
        }
    }

    private function _validateFormatOptions($entry, $config): void
    {
        $identifier = $config['identifier'];

        if (!is_array($entry)) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format.' must be of type array, if set."
            );
        }

        if ($this->format === '' && !isset($config['format'])) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format.' needs 'format' to have a keyword assigned, if set."
            );
        }

        $format = $config['format'];
        if ($this->format === '') {
            $this->_validateFormat($format, $config);
        }

        /** Special case */
        if ($format === 'date' && array_key_exists('strftime', $entry)) {
            throw new Exception (
                "'None' field '$identifier' configuration 'format.['date']['strftime']' 'strftime' is deprecated since " .
                "PHP 8.1.0 and thus not supported at this place, even still available in TYPO3."
            );
        }
        
        if (!array_key_exists($format, self::FORMAT_KEYWORDS)) {
            $formatsWithOptions = array_filter(self::FORMAT_KEYWORDS, function ($value, $key) {
                return is_array($value);
            }, ARRAY_FILTER_USE_BOTH);
            $formatsWithOptions = array_keys($formatsWithOptions);
            throw new Exception (
                "'None' field '$identifier' configuration 'format.[$format]' has no available options." .
                "Formats with options available are: " . implode(', ', $formatsWithOptions)
            );
        }

        $formatOptions = self::FORMAT_KEYWORDS[$format];
        
        if (!is_array($formatOptions)) {
            
            throw new Exception (
                "'None' field '$identifier' configuration 'format=>$format' has no available options."
            );
        }
        
        $i = 0;
        foreach ($entry as $key => $value) {
            if (!array_key_exists($key, $formatOptions)) {
                $formatOptions = array_keys($formatOptions);
                throw new Exception (
                    "'None' field '$identifier' configuration 'format.[$i]' is not a valid option. Valid options are: " .
                    implode(', ', $formatOptions)
                );
            }
            switch ($formatOptions[$key]) {
                case self::BOOL_TYPE:
                    if (!is_bool($value)) {
                        throw new Exception (
                            "'None' field '$identifier' configuration 'format.['$key']' must be of type boolean, if set."
                        );
                    }
                    break;
                case self::STRING_TYPE:
                    if (!is_string($value)) {
                        throw new Exception (
                            "'None' field '$identifier' configuration 'format.['$key']' must be of type string, if set."
                        );
                    }
                    break;
                case self::INTEGER_TYPE:
                    if (!$this->handleIntegers($value)) {
                        throw new Exception (
                            "'None' field '$identifier' configuration 'format.['$key']' must be of type integer, if set."
                        );
                    }
                    break;
                case self::FLOAT_TYPE:
                    if (!is_numeric($value)) {
                        throw new Exception (
                            "'None' field '$identifier' configuration 'format.['$key']' must be of type float, if set."
                        );
                    }
                    break;
                case self::FUNCTION:
                    $function = '_validateFormatOption_' . $key;
                    call_user_func([$this, $function], $value, $identifier);
                    break;
                default:
                    if (is_array($formatOptions[$key])) {
                        if (!in_array($value, $formatOptions[$key])) {
                            throw new Exception (
                                "'None' field '$identifier' configuration 'format.['$key']' $value is not a valid option. " .
                                "Valid options are: " . implode(', ', $formatOptions[$key])
                            );
                        }
                    }
                    break;
            }
            $i++;
        }
    }

    public function arrayToConfig(array $config): void
    {
        $properties = get_object_vars($this);
        foreach ($config as $configKey => $value) {
            if ($this->isValidConfig($properties, $configKey) || $configKey === 'format.') {
                if ($GLOBALS['CbBuilder']['config']['Strict'] === true) {
                    switch ($configKey) {
                        case 'format':
                            $this->_validateFormat($value, $config);
                            $this->format = $value;
                            break;
                        case 'format.':
                            $this->_validateFormatOptions($value, $config);
                            $this->format_ = $value;
                            break;
                        case 'size':
                            $this->validateInteger($value, $config, 'size', 'None', 10, 50);
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
        return parent::_configToElement('none', $properties);
    }
}

final class NoneField extends Fields
{
    protected NoneFieldConfig $config;

    private function _arrayToField(array $field): void
    {
        $this->__arrayToField('none', $field);
        $field['table'] = $this->table;
        $field['useExistingField'] = $this->useExistingField;
        $field['identifier'] = $this->identifier;
        $config = new NoneFieldConfig();
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