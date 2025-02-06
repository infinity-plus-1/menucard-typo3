<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'cb_builder',
    'description' => 'Create content blocks via console command and yaml a yaml file. Handy PHP classes are included and fluid helpers are provided automatically.',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'DS\\CbBuilder\\' => 'Classes/',
        ],
    ],
];
