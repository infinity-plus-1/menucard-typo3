<?php

declare(strict_types=1);

defined('TYPO3') or die();







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





