<?php

return [
    'ctrl' => [
        'title' => 'Food Category',
        'label' => 'header_col',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'previewRenderer' => 'TYPO3\CMS\ContentBlocks\Backend\Preview\PreviewRenderer',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'header_col',
        'iconfile' => 'EXT:menucardpackage/Resources/Public/Icons/content-menu-pages.svg',
        'versioningWS' => true,
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],

    'columns' => [
        'header_col' => [
            'l10n_mode' => 'prefixLangTitle',
            'label' => 'Column header',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'required' => true,
            ],
        ],
        'header_icon' => [
            'l10n_mode' => 'prefixLangTitle',
            'label' => 'Icon next to the header',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'menu_icon',
                'required' => true,
                'items' => [
                    [
                        'label' => 'Menu',
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
        'dishes' => [
            'label' => 'Dishes',
            'config' => [
                'type' => 'inline',
                'appearance' => [
                    'collapseAll' => true
                ],
                'foreign_table' => 'tx_foodmenu_dishes',
                'foreign_field' => 'parentid',
                'foreign_table_field' => 'parenttable',
                //'MM' => 'tx_styleguide_inline_mm_child_rel',
                'appearance' => [
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
                --div--;General, header_col, header_icon, --linebreak--, dishes,
                --div--;Visibility, sys_language_uid, l18n_parent,l18n_diffsource, hidden
            ',
        ],
    ],

];