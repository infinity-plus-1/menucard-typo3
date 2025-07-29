<?php

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths']['200'] = 'EXT:menucardpackage/Resources/Private/Mail/Layouts';

//&%!§(§?%&textpicture&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
call_user_func(function () {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
	'menucardpackage',
	'setup',
	'
tt_content.textpicture = FLUIDTEMPLATE
tt_content.textpicture {
    file = EXT:menucardpackage/ContentBlocks/textpicture/Templates/fe_preview.html
    partialRootPaths {
        5 = EXT:menucardpackage/ContentBlocks/textpicture/Partials/
    }
    layoutRootPaths {
        5 = EXT:menucardpackage/ContentBlocks/textpicture/Layouts/
    }
    dataProcessing.10 = DS\CbBuilder\DataProcessing\CbProcessor
    dataProcessing.10 {
        as = cbDataProcessor
    }
}
',
	'defaultContentRendering'
	);
});
//&%!§(§?%&textpicture&%?§)§!%&
//&%!§(§?%&menucards&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
call_user_func(function () {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
	'menucardpackage',
	'setup',
	'
tt_content.menucards = FLUIDTEMPLATE
tt_content.menucards {
    file = EXT:menucardpackage/ContentBlocks/menucards/Templates/fe_preview.html
    partialRootPaths {
        5 = EXT:menucardpackage/ContentBlocks/menucards/Partials/
    }
    layoutRootPaths {
        5 = EXT:menucardpackage/ContentBlocks/menucards/Layouts/
    }
    dataProcessing.10 = DS\CbBuilder\DataProcessing\CbProcessor
    dataProcessing.10 {
        as = cbDataProcessor
    }
}
',
	'defaultContentRendering'
	);
});
//&%!§(§?%&menucards&%?§)§!%&
//&%!§(§?%&blog_creator&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
call_user_func(function () {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
	'menucardpackage',
	'setup',
	'
tt_content.blog_creator = FLUIDTEMPLATE
tt_content.blog_creator {
    file = EXT:menucardpackage/ContentBlocks/blog_creator/Templates/fe_preview.html
    partialRootPaths {
        5 = EXT:menucardpackage/ContentBlocks/blog_creator/Partials/
    }
    layoutRootPaths {
        5 = EXT:menucardpackage/ContentBlocks/blog_creator/Layouts/
    }
    dataProcessing.10 = DS\CbBuilder\DataProcessing\CbProcessor
    dataProcessing.10 {
        as = cbDataProcessor
    }
}
',
	'defaultContentRendering'
	);
});
//&%!§(§?%&blog_creator&%?§)§!%&