<?php

declare(strict_types=1);
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$GLOBALS['TCA']['tt_content']['columns']['menu_cols'] = $GLOBALS['TCA']['tx_foodmenu_main']['columns']['menu_cols'];
$key = 'foodmenu';
ExtensionManagementUtility::addTcaSelectItem
(
    'tt_content',
    'CType',
    [
        'label' => 'Food menu',
        'description' => 'Create a menu card in form of: Element->Column->Row',
        'value' => $key,
        'icon' => 'content-menu-pages',
        'group' => 'default',
    ],
    'menucard_featuresgrid',
    'after'
);

$GLOBALS['TCA']['tt_content']['types'][$key] =
[
    //'previewRenderer' => menucardvendor\menucardpackage\Backend\Preview\FoodmenuPreviewRenderer::class,
    'showitem' => '
            --palette--;;header,defaultButtonText,bodytext,menu_cols,
    ',
    'columnsOverrides' =>
    [
        'header' =>
        [
            'label' => 'Menu header',
            'description' => 'Spend some shining words to highlight your menu.',
            'config' =>
            [
            ]
        ],
        'defaultButtonText' => [
            'label' => 'Icon next to the header',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'menu_icon',
                'required' => true,
                'items' => [
                    [
                        'label' => 'Menu (recommended)',
                        'value' => 'menu_icon',
                    ],
                    [
                        'label' => 'Noodles',
                        'value' => 'noodles_icon',
                    ],
                    [
                        'label' => 'Rice',
                        'value' => 'rice_icon',
                    ],
                    [
                        'label' => 'Steaks and meat',
                        'value' => 'steaks_icon',
                    ],
                    [
                        'label' => 'Drinks',
                        'value' => 'drinks_icon',
                    ],
                    [
                        'label' => 'Salads',
                        'value' => 'salads_icon',
                    ],
                    [
                        'label' => 'Pizza',
                        'value' => 'pizza_icon',
                    ],
                    [
                        'label' => 'Wines',
                        'value' => 'wines_icon',
                    ],
                    [
                        'label' => 'Burgers',
                        'value' => 'burgers_icon',
                    ]
                ],
            ],
        ],
        'bodytext' =>
        [
            'label' => 'Food menu description',
            'description' => 'Promote the available dishes',
            'config' => [
                'type' => 'input',
                'required' => true,
            ]
        ]
    ]
];

?>