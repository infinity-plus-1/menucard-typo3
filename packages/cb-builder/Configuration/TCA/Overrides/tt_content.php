<?php

declare(strict_types=1);

defined('TYPO3') or die();


//&%!§(§?%&new_content_block&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem
(
    'tt_content',
    'CType',
    [
        'label' => 'New Content Block',        //The name of the content block
        'description' => 'This is the content block New Content Block',  //The description of the content block
        'value' => 'new_content_block',  //The identifier of the content block
        'icon' => '',                   //The icon can be filled later
        'group' => 'default',       //The content block group, 'default' is the standard
    ],
    'textmedia',            //Here comes the identifier of the content block where to insert the new content block in the list
    'after'                    //The positioning can be 'before' or 'after'
);

$GLOBALS['TCA']['tt_content']['types']['new_content_block'] =
[
    'showitem' => '--palette--;;header,bodytext',
    'columnsOverrides' =>
    [
        'header' => [
    'label' => 'Element header',
    'description' => 'Write your fancy heading line here.',
    'config' =>
    [
        'type' => 'input',
        'required' => true
    ]
],
'bodytext' => [
    'label' => 'Element textarea',
    'description' => 'Let your creativity take its course...',
    'config' => [
        'type' => 'text',
        'enableRichtext' => true
    ]
]
    ]
];
//&%!§(§?%&new_content_block&%?§)§!%&
