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
        'dishes' => [
            'label' => 'Dishes',
            'config' => [
                'type' => 'inline',
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
                --div--;General, header_col, --linebreak--, dishes,
                --div--;Visibility, sys_language_uid, l18n_parent,l18n_diffsource, hidden
            ',
        ],
    ],

];