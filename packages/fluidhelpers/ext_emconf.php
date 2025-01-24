<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'fluidhelpers',
    'description' => 'Handy fluid helper classes',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'DS\\fluidHelpers\\' => 'Classes/',
        ],
    ],
];
