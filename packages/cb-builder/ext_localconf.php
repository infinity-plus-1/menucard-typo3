<?php


//&%!§(§?%&new_content_block&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
call_user_func(function () {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
	'cb_builder',
	'setup',
	'
tt_content.new_content_block = FLUIDTEMPLATE
tt_content.new_content_block {
    file = EXT:cb_builder/ContentBlocks/new_content_block/Templates/fe_preview.html
    partialRootPaths {
        5 = EXT:cb_builder/ContentBlocks/new_content_block/Partials/
    }
    layoutRootPaths {
        5 = EXT:cb_builder/ContentBlocks/new_content_block/Layouts/
    }
}
',
	'defaultContentRendering'
	);
});
//&%!§(§?%&new_content_block&%?§)§!%&
