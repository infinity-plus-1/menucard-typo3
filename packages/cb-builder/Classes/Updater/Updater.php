<?php

declare(strict_types=1);

namespace DS\CbBuilder\Updater;

use DS\CbBuilder\FieldBuilder\FieldBuilder;
use DS\CbBuilder\Wrapper\Wrapper;
use Exception;
use Symfony\Component\Yaml\Yaml;

class UpdaterException extends Exception {}

final class Updater
{

    public static function loadConfig(): void
    {
        $cbConfig = Yaml::parseFile(__DIR__ . '/../../Configuration/cbconfig.yaml');
        $GLOBALS['CbBuilder'] = $cbConfig;
    }

    public static function updateTyposcript(string $path, string $extension, string $identifier): void
    {
        $typoscript = $path . '/Configuration/TypoScript/setup.typoscript';
        if (!file_exists($typoscript)) {
            throw new UpdaterException('');
        }
        $typoscript = Wrapper::extract(file_get_contents($typoscript), $identifier);
        $func = 
        "call_user_func(function () {\n" .
        "\t\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(\n" .
        "\t'$extension',\n" .
        "\t'setup',\n" .
        "\t'$typoscript',\n" .
        "\t'defaultContentRendering'\n" .
        "\t);\n" .
        "});";
        $func = Wrapper::wrap($func, $identifier);
        $extLocalConf = '';
        if (file_exists($path . '/ext_localconf.php')) {
            $extLocalConf = file_get_contents($path . '/ext_localconf.php') . $func;
        } else {
            $extLocalConf = "<?php\n\n" . $func;
        }
        file_put_contents($path . '/ext_localconf.php', $extLocalConf);
    }

    public static function updateFields($path, $identifier): void
    {
        Updater::loadConfig();
        $fieldBuilder = new FieldBuilder($path, $identifier);
        $fieldBuilder->buildFields();
    }

}