<?php

declare(strict_types=1);

defined('TYPO3') or die();


//§%!&(&?%§EXT_LOCALCONF§%!&)&?%§ Do not modify this part of the code unless you want to uninstall the entire extension.
call_user_func(function () {
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1742310610]    = [
    'nodeName' => 'attributePalette',
    'priority' => 40,
    'class' => DS\CbBuilder\Form\Element\AttributePaletteElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
    DS\CbBuilder\Hook\AttributePaletteHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =
    DS\CbBuilder\Hook\AttributePaletteHook::class;
});

//§%!&(&?%§EXT_LOCALCONF§%!&)&?%§

