<?php

declare(strict_types=1);

namespace DS\CbBuilder\FieldBuilder\Fields;

use Exception;

class ConfigException extends Exception {}

class Config
{
    protected array $behaviour;
    protected array $fieldControl;
    protected array $fieldInformation;
    protected array $fieldWizard;

    protected function isValidLeveledConfig(array $allowed, string $config, string $type): bool
    {
        if (array_key_exists($config, $allowed) && (in_array('all', $allowed[$config]) || in_array($type, $allowed[$config]))) {
            return true;
        }
        return false;
    }

    protected function isValidConfig(array $properties, string $config): bool
    {
        // if (array_key_exists($config, $properties)) {
        //     if (is_array())
        // }
        return array_key_exists($config, $properties);
    }

    

    protected function _configToElement(string $type, array $properties): array
    {
        $config = [
            'type' => $type
        ];
        foreach ($properties as $property => $value) {
            if (is_string($value)) {
                if ($value != '') $config[$property] = $value;
            } else if (is_numeric($value)) {
                if ($value >= 0) $config[$property] = $value;
            } else if (is_array($value)) {
                if (!empty($value)) $config[$property] = $value;
            }
            else $config[$property] = $value;
        }
        return $config;
    }

    protected function setBehaviour(array $behaviour, string $type): void
    {
        $allowed = [
            'allowLanguageSynchronization' => [
                'all'
            ]
        ];
        foreach ($behaviour as $config => $value) {
            if ($this->isValidLeveledConfig($allowed, $config, $type)) {
                $this->behaviour[$config] = $value;
            }
        }
    }

    public function getBehaviour(): array
    {
        return $this->behaviour;
    }
}