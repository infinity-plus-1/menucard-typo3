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
            --palette--;test2;test,defaultButtonText,bodytext,menu_cols,
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
    ],
];
$GLOBALS['TCA']['tt_content']['palettes']['test3'] =
[
    'showitem' => 'header'
];










//§%!&(&?%§CB_INDEX_PALETTE§%!&)&?%§ DO NOT TOUCH THIS PART OF CODE UNLESS YOU WANT TO DEINSTALL THE WHOLE EXTENSION
$GLOBALS['TCA']['tt_content']['columns']['cb_index'] = [
    "label" => "Cb Settings",
    "description" => "Modify the content element to your needings.",
    "config" => [
        "type" => "user",
        "renderType" => "attributePalette"
    ]
];
//§%!&(&?%§CB_INDEX_PALETTE§%!&)&?%§

//&%!§(§?%&textpicture&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem (
	'tt_content',
	'CType',
	[
		"label" => "Textpicture",
		"description" => "Adjustable images and textarea.",
		"value" => "textpicture",
		"icon" => "textpicture_icon",
		"group" => "default",
	],
	'textmedia',
	'after'
);


$GLOBALS['TCA']['tt_content']['types']['textpicture'] = [
	"columnsOverrides" => [
		"header" => [
			"label" => "header",
			"description" => "The header for this element.",
			"config" => [
				"type" => "input",
				"required" => true,
			],
		],
		"subheader" => [
			"description" => "The subheader.",
			"label" => "subheader",
			"config" => [
				"type" => "input",
			],
		],
		"image" => [
			"label" => "image",
			"config" => [
				"type" => "file",
				"allowed" => "common-image-types",
			],
		],
		"bodytext" => [
			"label" => "bodytext",
			"config" => [
				"type" => "text",
				"enableRichtext" => true,
			],
		],
		"header_link" => [
			"label" => "header_link",
			"config" => [
				"type" => "link",
				"allowedTypes" => [
					"*",
				],
			],
		],
	],
	"showitem" => "cb_index,header,--linebreak--,subheader,bodytext,image,header_link",
];

//&%!§(§?%&textpicture&%?§)§!%&
//&%!§(§?%&menucards&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem (
	'tt_content',
	'CType',
	[
		"label" => "Menucards",
		"description" => "Create a menucard",
		"value" => "menucards",
		"icon" => "menucards_icon",
		"group" => "default",
	],
	'textmedia',
	'after'
);


$GLOBALS['TCA']['tt_content']['columns']['menucard_columns'] = [
	"label" => "menucard_columns",
	"description" => "Define columns for specific types of food. Like Steaks, Noodles, Drinks, etc.",
	"config" => [
		"type" => "inline",
		"appearance" => [
			"levelLinksPosition" => "both",
		],
		"foreign_field" => "menucard_columns",
		"foreign_table" => "menucard_columns",
	],
];

$GLOBALS['TCA']['tt_content']['types']['menucards'] = [
	"columnsOverrides" => [
		"header" => [
			"label" => "header",
			"description" => "The header for this element.",
			"config" => [
				"type" => "input",
				"required" => true,
				"max" => 255,
			],
		],
		"bodytext" => [
			"label" => "Element textarea",
			"description" => "Let your creativity take its course...",
			"config" => [
				"type" => "text",
				"enableRichtext" => true,
			],
		],
		"subheader" => [
			"description" => "A short description.",
			"label" => "subheader",
			"config" => [
				"type" => "input",
				"max" => 255,
			],
		],
	],
	"showitem" => "cb_index,header,--linebreak--,subheader,menucard_columns",
];

//&%!§(§?%&menucards&%?§)§!%&
//&%!§(§?%&blog_creator&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem (
	'tt_content',
	'CType',
	[
		"label" => "Blog Creator",
		"description" => "Easily add creator information for a blog post.",
		"value" => "blog_creator",
		"icon" => "blog_creator_icon",
		"group" => "default",
	],
	'textmedia',
	'after'
);


$GLOBALS['TCA']['tt_content']['columns']['date'] = [
	"label" => "date",
	"description" => "The date and time when the post was created.",
	"config" => [
		"type" => "datetime",
	],
];

$GLOBALS['TCA']['tt_content']['types']['blog_creator'] = [
	"columnsOverrides" => [
		"header" => [
			"description" => "(Optional) The name of the Author.",
			"label" => "header",
			"config" => [
				"type" => "input",
				"required" => false,
			],
		],
		"bodytext" => [
			"description" => "Add some information about the author.",
			"label" => "bodytext",
			"config" => [
				"type" => "text",
				"enableRichtext" => true,
				"required" => true,
			],
		],
		"image" => [
			"description" => "(Optional) Upload the image of the author.",
			"label" => "image",
			"config" => [
				"type" => "file",
				"allowed" => "common-image-types",
			],
		],
	],
	"showitem" => "cb_index,header,--linebreak--,bodytext,image",
];

//&%!§(§?%&blog_creator&%?§)§!%&
