<?php

return [
    'ctrl' => [
        'title' => 'Food menu',
        'label' => 'header_main',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'header_main,desc_main',
        'iconfile' => 'EXT:menucardpackage/Resources/Public/Icons/content-menu-pages.svg',
        'versioningWS' => true,
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],

    'columns' => [
        'header_main' => [
            'l10n_mode' => 'prefixLangTitle',
            'label' => 'Header',
            'description' => 'Main header for the food menu',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'required' => true,
            ],
        ],
        'desc_main' => [
            'label' => 'Food menu description',
            'description' => 'Promote the available dishes',
            'config' => [
                'type' => 'input',
                'required' => true,
            ],
        ],
        'menu_cols' => [
            'label' => 'Food category',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_foodmenu_columns',
                'foreign_field' => 'parentid',
                'foreign_table_field' => 'parenttable',
                //'MM' => 'tx_styleguide_inline_mm_child_rel',
                'appearance' => [
                    'collapseAll' => true,
                    'showSynchronizationLink' => 1,
                    'showAllLocalizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                ],
            ],
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => '
                --div--;General, header_main, header_icon, --linebreak--, desc_main, --linebreak--, menu_cols,
                --div--;Visibility, sys_language_uid, l18n_parent,l18n_diffsource, hidden
            ',
        ],
    ],

];

?>