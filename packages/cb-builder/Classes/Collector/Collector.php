<?php

declare(strict_types=1);

namespace DS\CbBuilder\Collector;

use Exception;
use Symfony\Component\Yaml\Yaml;

class CollectorException extends Exception {}

final class Collector
{
    public static function collectContentBlocks(?bool $reducedOutput = true): array
    {
        $contentBlocks = Yaml::parseFile(__DIR__ . '/../../ContentBlocks/contentBlocks.yaml');
        if (!$reducedOutput) return $contentBlocks['contentBlocks'];
        return array_map(function ($contentBlock) {
            return $contentBlock['name'] . ' -> ' . $contentBlock['identifier'];
        }, $contentBlocks['contentBlocks']);
    }
}