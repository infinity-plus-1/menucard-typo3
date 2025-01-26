<?php

return [
    'ctrl' => [
        'title' => 'Dish',
        'label' => 'header_item',
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
        'searchFields' => 'header_item,desc_item',
        'iconfile' => 'EXT:menucardpackage/Resources/Public/Icons/content-menu-pages.svg',
        'versioningWS' => true,
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],

    'columns' => [
        'header_item' => [
            'l10n_mode' => 'prefixLangTitle',
            'label' => 'Dish header',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'required' => true,
            ],
        ],
        'desc_item' => [
            'label' => 'Dish description',
            'description' => 'Write something graceful about this dish!',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
            ],
        ],
        'price_item' => [
            'label' => 'Item price',
            'config' => [
                'type' => 'number',
                'size' => 10,
                'autocomplete' => true,
                'format' => 'decimal',
                'default' => '0.0',
                'required' => true
            ],
        ],
        'image_item' => [
            'label' => 'Image of the dish',
            'config' => [
                'type' => 'file',
                'minitems' => 1,
                'maxitems' => 1,
                'allowed' => 'common-image-types'
            ],
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => '
                --div--;General, header_item, --linebreak--, desc_item, --linebreak--, price_item, --linebreak--, image_item,
                --div--;Visibility, sys_language_uid, l18n_parent,l18n_diffsource, hidden
            ',
        ],
    ],

];