<?php

declare(strict_types=1);

namespace DS\CbBuilder\BasicBuilder;

use DS\CbBuilder\FileCreater\FileCreater;
use DS\CbBuilder\Updater\Updater;
use Exception;
use Symfony\Component\Yaml\Yaml;

class BasicBuilderException extends Exception {}

final class BasicBuilder
{
    public static function makeContentBlocksDir($path): void
    {
        if (is_dir($path)) {
            if (!is_dir($path . '/' . 'ContentBlocks')) {
                $created = mkdir($path . '/' . 'ContentBlocks');
                if (!$created) {
                    throw new BasicBuilderException (
                        "Directory 'ContentBlocks' can't be created with path $path." .
                        " Please check the path and the permissions or try again."
                    );
                }
            }
        } else {
            throw new BasicBuilderException (
                "No extension found under path $path." .
                " Please check the path and the permissions or try again."
            );
        }
    }

    public static function _addContentBlockToList($path, $extension, $namespace, $name, $identifier, $group): void
    {
        // $entry =    "\n  $identifier:\n   identifier: $identifier\n    name: $name\n    path: $path\n    extension: $extension\n    " .
        //             "namespace: $namespace\n    group: $group";
        $yamlPath = __DIR__ . "/../../ContentBlocks/contentBlocks.yaml";
        $entry = [
            'identifier' => $identifier,
            'name' => $name,
            'path' => $path,
            'extension' => $extension,
            'namespace' => $namespace,
            'group' => $group
        ];
        $yaml = Yaml::parseFile($yamlPath);
        $yaml['contentBlocks'][$identifier] = $entry;
        $yaml = Yaml::dump($yaml, PHP_INT_MAX, 2);
        file_put_contents($yamlPath, $yaml);
    }

    public static function buildBasicStructure($path, $extension, $namespace, $name, $identifier, $desc, $placeAt, $position, $group, $include): void
    {
        dump($path);
        dump($identifier);
        FileCreater::makeBackendPreview($path, $extension, $identifier);
        FileCreater::makeFrontendPreview($path, $extension, $identifier);
        FileCreater::makeTtContent($path, $identifier, $name, $desc, $placeAt, $position, $group, $include);
        FileCreater::makeFieldsYaml($path, $identifier, $name, $desc, $placeAt, $position, $group, $include);
        //Updater::updateFields($path, $identifier);
        BasicBuilder::_addContentBlockToList($path, $extension, $namespace, $name, $identifier, $group);
    }
}

?>