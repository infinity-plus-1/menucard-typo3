<?php


//&%!§(§?%&menucards&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
return [
	"ctrl" => [
		"label" => "uid",
		"label_alt" => "dishHeader, dishDesc",
		"descriptionColumn" => "",
		"sortby" => "sorting",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"editlock" => "editlock",
		"title" => "menucard_rows",
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
		"dishHeader" => [
			"label" => "dishHeader",
			"description" => "The name of the dish",
			"config" => [
				"type" => "input",
				"max" => 255,
				"required" => true,
			],
		],
		"dishDesc" => [
			"label" => "dishDesc",
			"description" => "A short description.",
			"config" => [
				"type" => "input",
				"max" => 255,
			],
		],
		"price" => [
			"label" => "price",
			"config" => [
				"type" => "number",
				"format" => "decimal",
			],
		],
		"image" => [
			"label" => "image",
			"config" => [
				"type" => "file",
				"allowed" => "common-image-types",
			],
		],
	],
	'types' => [
		'0' => [
			"columnsOverrides" => [
			],
			"showitem" => "cb_index,dishHeader,dishDesc,price,image",
		],
	],
	'palettes' => [
	],
];

//&%!§(§?%&menucards&%?§)§!%&
