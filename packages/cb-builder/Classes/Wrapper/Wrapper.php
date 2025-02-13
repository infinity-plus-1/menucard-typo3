<?php

declare(strict_types=1);

namespace DS\CbBuilder\Wrapper;

use DS\CbBuilder\Collector\Collector;
use DS\CbBuilder\FieldBuilder\FieldBuilder;
use Exception;
use Symfony\Component\Yaml\Yaml;

class WrapperException extends Exception {}

final class Wrapper
{
    const TOKEN_FILE_LIST = [
        '/ext_localconf.php',
        '/Configuration/page.tsconfig',
        '/Configuration/TypoScript/setup.typoscript',
        '/Configuration/TCA/Overrides/tt_content.php'
    ];

    private static function _changeContentBlockWrapTokens
    (
        array $contentBlock,
        string $startToken,
        string $endToken,
        string $oldStartToken,
        string $oldEndToken
    ): void
    {
        $path = $contentBlock['path'];
        foreach (Wrapper::TOKEN_FILE_LIST as $file) {
            if (file_exists($path . $file)) {
                $fileContent = file_get_contents($path . $file);
                $fileContent = str_replace($oldStartToken, $startToken, str_replace($oldEndToken, $endToken, $fileContent));
                file_put_contents($path . $file, $fileContent);
            }
        }
    }

    public static function changeWrapTokens(string $startToken, string $endToken): void
    {
        $cbConfig = Yaml::parseFile(__DIR__ . '/../../Configuration/cbconfig.yaml');
        $oldStartToken = $cbConfig['config']['Tokens']['identifierOpeningToken'];
        $oldEndToken = $cbConfig['config']['Tokens']['identifierClosingToken'];
        $cbConfig['config']['Tokens']['identifierOpeningToken'] = $startToken;
        $cbConfig['config']['Tokens']['identifierClosingToken'] = $endToken;
        $contentBlocks = Collector::collectContentBlocks(false);
        foreach ($contentBlocks as $contentBlock) {
            Wrapper::_changeContentBlockWrapTokens($contentBlock, $startToken, $endToken, $oldStartToken, $oldEndToken);
        }
    }

    public static function getWrapTokens(): array
    {
        return Yaml::parseFile(__DIR__ . '/../../Configuration/cbconfig.yaml')['config']['Tokens'];
    }

    public static function getWrapBoundary(string $content, string $identifier): array
    {
        $tokens = Wrapper::getWrapTokens();
        $startToken = $tokens['identifierOpeningToken'];
        $endToken = $tokens['identifierClosingToken'];
        $start = strpos($content, $startToken . $identifier . $endToken) - 2;
        $startNewline = strpos($content, "\n", $start);
        $end = strpos($content, $startToken . $identifier . $endToken, $startNewline) - 2;
        $endNewline = strpos($content, "\n", $end);
        return ['start' => $start, 'startNewline' => $startNewline, 'end' => $end, 'endNewline' => $endNewline];
    }

    public static function wrap(string $content, string $identifier): string
    {
        return  "//&%!§(§?%&" . $identifier . "&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT " .
                "- NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!\n" . $content . "\n//&%!§(§?%&" . $identifier . "&%?§)§!%&\n";
    }

    public static function extract(string $content, string $identifier): string
    {
        $boundary = Wrapper::getWrapBoundary($content, $identifier);
        return substr($content, $boundary['startNewline'], ($boundary['end'] - $boundary['startNewline']));
    }

    public static function inject(string $content, string $identifier, string $needle): string
    {
        $boundary = Wrapper::getWrapBoundary($content, $identifier);
        $before = substr($content, 0, $boundary['startNewline']);
        $after = substr($content, $boundary['end'], strlen($content));
        return $before . $needle . $after;
    }
}