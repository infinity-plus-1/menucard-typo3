<?php

declare(strict_types=1);

namespace DS\CbBuilder\FileCreater;

use DS\CbBuilder\Updater\Updater;
use DS\CbBuilder\Wrapper\Wrapper;
use Exception;

class FileCreaterException extends Exception {}

final class FileCreater
{

    public static function makeBackendPreview($path, $extension, $identifier): void
    {
        $extension = str_replace('-', '_', $extension);
        $setting = "\nmod.web_layout.tt_content.preview.$identifier = EXT:$extension/ContentBlocks/$identifier/Templates/be_preview.html\n";
        $setting = Wrapper::wrap($setting, $identifier);
        if (!is_dir($path . '/Configuration')) {
            if (!mkdir($path . '/Configuration')) {
                throw new FileCreaterException('');
            }
        }

        if (file_exists($path . '/Configuration/page.tsconfig')) {
            $pageTsConfig = file_get_contents($path . '/Configuration/page.tsconfig');
            file_put_contents($path . '/Configuration/page.tsconfig', $pageTsConfig . $setting);
        } else {
            file_put_contents($path . '/Configuration/page.tsconfig', $setting);
        }

        $previewContent = file_get_contents(__DIR__ . '/../../Templates/be_preview.html');
        $previewContent = str_replace('{%%Identifier%%}', $identifier, $previewContent);
        if (!is_dir($path . "/ContentBlocks/$identifier/Templates")) {
            if (!mkdir($path . "/ContentBlocks/$identifier/Templates", 0777, true)) {
                throw new FileCreaterException('');
            }
        }
        file_put_contents($path . "/ContentBlocks/$identifier/Templates/be_preview.html", $previewContent);
    }

    public static function makeFrontendPreview($path, $extension, $identifier): void
    {
        $extension = str_replace('-', '_', $extension);
        $templatesPath = __DIR__ . '/../../Templates/';
        $setupTyposcript = file_get_contents($templatesPath . 'setup.typoscript');
        $fePreview = file_get_contents($templatesPath . 'fe_preview.html');
        $fePreview = str_replace('{%%Identifier%%}', $identifier, $fePreview);
        $layoutDefault = file_get_contents($templatesPath . 'layoutDefault.html');

        if (!is_dir($path . '/Configuration/TypoScript')) {
            if (!mkdir($path . '/Configuration/TypoScript', 0777, true)) {
                throw new FileCreaterException('');
            }
        }
        $setupTyposcript = str_replace('{%%Extension%%}', $extension, str_replace('{%%Identifier%%}', $identifier, $setupTyposcript));
        $setupTyposcript = Wrapper::wrap($setupTyposcript, $identifier);

        if (!is_dir($path . '/Configuration/TypoScript/')) {
            if (!mkdir($path . '/Configuration/TypoScript/')) {
                throw new FileCreaterException('');
            }
        }

        if (file_exists($path . '/Configuration/TypoScript/setup.typoscript')) {
            $setupTyposcript = file_get_contents($path . '/Configuration/TypoScript/setup.typoscript') . $setupTyposcript;
        }
        file_put_contents($path . '/Configuration/TypoScript/setup.typoscript', $setupTyposcript);

        Updater::updateTyposcript($path, $extension, $identifier);


        if (!is_dir($path . "/ContentBlocks/$identifier/Templates")) {
            if (!mkdir($path . "/ContentBlocks/$identifier/Templates", 0777, true)) {
                throw new FileCreaterException('');
            }
        }
        file_put_contents($path . "/ContentBlocks/$identifier/Templates/fe_preview.html", $fePreview);

        if (!is_dir($path . "/ContentBlocks/$identifier/Layouts")) {
            if (!mkdir($path . "/ContentBlocks/$identifier/Layouts", 0777, true)) {
                throw new FileCreaterException('');
            }
        }
        file_put_contents($path . "/ContentBlocks/$identifier/Layouts/Default.html", $layoutDefault);
    }

    public static function makeTtContent($path, $identifier, $name, $desc, $placeAt, $position, $group, $include): void
    {
        if (is_dir($path . '/Configuration')) {
            if (!is_dir($path . '/Configuration/TCA')) {
                if (!mkdir($path . '/Configuration/TCA', 0777, true)) {
                    throw new FileCreaterException('');
                }
            }
            if (!is_dir($path . '/Configuration/TCA/Overrides')) {
                if (!mkdir($path . '/Configuration/TCA/Overrides')) {
                    throw new FileCreaterException('');
                }
            }
            $ttContent = '';
            $ttContentTemplate = file_get_contents(__DIR__ . '/../../Templates/tt_content.php');
            if (file_exists($path . '/Configuration/TCA/Overrides/tt_content.php')) {
                $ttContent = file_get_contents($path . '/Configuration/TCA/Overrides/tt_content.php');
                $ttContentTemplate = str_replace (
                    '{%%position%%}',
                    $position,
                    str_replace (
                        '{%%placeAt%%}',
                        $placeAt,
                        str_replace (
                            '{%%desc%%}',
                            $desc,
                            str_replace (
                                '{%%name%%}',
                                $name,
                                str_replace (
                                    '{%%identifier%%}',
                                    $identifier,
                                    str_replace (
                                        '{%%group%%}',
                                        $group,
                                        $ttContentTemplate
                                    )
                                )
                            )
                        )
                    )
                );
            } else {
                $ttContent =    "<?php\n\ndeclare(strict_types=1);\n\ndefined('TYPO3') or die();\n\n";
                $ttContentTemplate = str_replace (
                    '{%%position%%}',
                    $position,
                    str_replace (
                        '{%%placeAt%%}',
                        $placeAt,
                        str_replace (
                            '{%%desc%%}',
                            $desc,
                            str_replace (
                                '{%%name%%}',
                                $name,
                                str_replace (
                                    '{%%identifier%%}',
                                    $identifier,
                                    str_replace (
                                        '{%%group%%}',
                                        $group,
                                        $ttContentTemplate
                                    )
                                )
                            )
                        )
                    )
                );
            }
            $override = '';
            if ($include === 'yes') {
                $include = '--palette--;;header,bodytext';
                $override = file_get_contents(__DIR__ . '/../../Templates/columnsOverrides.temp');
            } else {
                $include = '';
            }
            $ttContentTemplate = str_replace(
                '{%%basicInclude%%}',
                $include, 
                str_replace(
                    '{%%columnsOverrides%%}',
                    $override,
                    $ttContentTemplate
                )
            );
            $ttContentTemplate = Wrapper::wrap($ttContentTemplate, $identifier);
            $ttContent .= $ttContentTemplate;
            file_put_contents($path . '/Configuration/TCA/Overrides/tt_content.php', $ttContent);
        }
    }

    public static function makeFieldsYaml($path, $identifier, $name, $desc, $placeAt, $position, $group, $include): void
    {
        $fields = file_get_contents(__DIR__ . '/../../Templates/fields.yaml');

        $fields = str_replace (
            '{%%name%%}', $name, str_replace (
                '{%%identifier%%}', $identifier, str_replace (
                    '{%%desc%%}', $desc, str_replace (
                        '{%%group%%}', $group, str_replace (
                            '{%%placeAt%%}', $placeAt, str_replace (
                                '{%%position%%}', $position, $fields
                            )
                        )
                    )
                )
            )
        );

        if ($include) {
            $basicInclude = "\n  - identifier: header\n    useExistingField: true\n    type: Text\n    required: true" .
                            "\n    description: The header for this element.\n  - type: Linebreak\n  - identifier: bodytext" .
                            "\n    useExistingField: true\n    type: Textarea\n    enableRichtext: true" .
                            "\n    description: The main text area of this element.";
            $fields = str_replace('{%%basicInclude%%}', $basicInclude, $fields);
        } else {
            $fields = str_replace('{%%basicInclude%%}', '', $fields);
        }
        file_put_contents($path . "/ContentBlocks/$identifier/fields.yaml", $fields);
    }
}

?>