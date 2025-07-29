<?php


//&%!§(§?%&menucards&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
return [
	"ctrl" => [
		"label" => "uid",
		"label_alt" => "headerType",
		"descriptionColumn" => "",
		"sortby" => "sorting",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"editlock" => "editlock",
		"title" => "menucard_columns",
		"delete" => "deleted",
		"versioningWS" => true,
		"groupName" => "content",
		"hideAtCopy" => true,
		"prependAtCopy" => "LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.prependAtCopy",
		"copyAfterDuplFields" => "sys_language_uid",
		"useColumnsForDefaultValues" => "sys_language_uid",
		"transOrigPointerField" => "l18n_parent",
		"transOrigDiffSourceField" => "l18n_diffsource",
		"languageField" => "sys_language_uid",
		"translationSource" => "l10n_source",
		"previewRenderer" => "TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer",
		"enablecolumns" => [
			"disabled" => "hidden",
			"starttime" => "starttime",
			"endtime" => "endtime",
			"fe_group" => "fe_group",
		],
		"typeicon_classes" => [
			"default" => "mimetypes-x-content-text",
			"header" => "mimetypes-x-content-header",
		],
		"searchFields" => "header,header_link,subheader,bodytext,pi_flexform",
		"security" => [
			"ignorePageTypeRestriction" => true,
		],
	],
	'columns' => [
		"headerType" => [
			"label" => "headerType",
			"description" => "The column header",
			"config" => [
				"type" => "input",
			],
		],
		"columnIcon" => [
			"label" => "columnIcon",
			"config" => [
				"type" => "select",
				"renderType" => "selectSingle",
				"items" => [
					[
						"label" => "Drinks",
						"value" => "drinks",
					],
					[
						"label" => "Appetisers",
						"value" => "appetisers",
					],
					[
						"label" => "Pasta",
						"value" => "pasta",
					],
					[
						"label" => "Salads",
						"value" => "salads",
					],
					[
						"label" => "Burgers",
						"value" => "burgers",
					],
					[
						"label" => "Pizza",
						"value" => "pizza",
					],
					[
						"label" => "Steaks",
						"value" => "steaks",
					],
				],
			],
		],
		"menucard_rows" => [
			"label" => "menucard_rows",
			"description" => "Add dishes of every food category.",
			"config" => [
				"type" => "inline",
				"appearance" => [
					"levelLinksPosition" => "both",
				],
				"foreign_field" => "menucard_rows",
				"foreign_table" => "menucard_rows",
			],
		],
	],
	'types' => [
		'0' => [
			"columnsOverrides" => [
			],
			"showitem" => "cb_index,headerType,columnIcon,menucard_rows",
		],
	],
	'palettes' => [
	],
];

//&%!§(§?%&menucards&%?§)§!%&
