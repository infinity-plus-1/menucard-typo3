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
    'showitem' => '
            --palette--;;header,bodytext,menu_cols
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
        'bodytext' =>
        [
            'label' => 'Menu description',
            'description' => 'Promote your menu card wisely with some graceful words.',
            'config' =>
            [
                'enableRichtext' => true,
                'richtextConfiguration' => 'full',
            ]
        ],
    ],
];

?>