<?php
return [
    'BE' => [
        'debug' => true,
        'installToolPassword' => '$argon2i$v=19$m=65536,t=16,p=1$eDZZelE3QUlZb3FZSExYdA$1Tcs6JQ6fgld4PyMrNXLoEPVMuMnV536DP6U7P5fBEk',
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
    ],
    'DB' => [
        'Connections' => [
            'Default' => [
                'charset' => 'utf8',
                'driver' => 'mysqli',
            ],
        ],
    ],
    'EXTENSIONS' => [
        'backend' => [
            'backendFavicon' => '',
            'backendLogo' => '',
            'loginBackgroundImage' => '',
            'loginFootnote' => '',
            'loginHighlightColor' => '',
            'loginLogo' => '',
            'loginLogoAlt' => '',
        ],
        'extensionmanager' => [
            'automaticInstallation' => '1',
            'offlineMode' => '0',
        ],
        'indexed_search' => [
            'catdoc' => '/usr/bin/',
            'deleteFromIndexAfterEditing' => '1',
            'disableFrontendIndexing' => '0',
            'flagBitMask' => '192',
            'fullTextDataLength' => '0',
            'ignoreExtensions' => '',
            'indexExternalURLs' => '0',
            'maxExternalFiles' => '5',
            'minAge' => '24',
            'pdf_mode' => '20',
            'pdftools' => '/usr/bin/',
            'ppthtml' => '/usr/bin/',
            'unrtf' => '/usr/bin/',
            'unzip' => '/usr/bin/',
            'useMysqlFulltext' => '0',
            'xlhtml' => '/usr/bin/',
        ],
        'redirects' => [
            'showCheckIntegrityInfoInReports' => '1',
            'showCheckIntegrityInfoInReportsSeconds' => '86400',
        ],
        'scheduler' => [
            'maxLifetime' => '1440',
        ],
        'styleguide' => [
            'boolean_1' => '0',
            'boolean_2' => '1',
            'boolean_3' => '',
            'boolean_4' => '0',
            'color_1' => 'black',
            'color_2' => '#000000',
            'color_3' => '000000',
            'color_4' => '',
            'compat_default_1' => 'value',
            'compat_default_2' => '',
            'compat_input_1' => 'value',
            'compat_input_2' => '',
            'int_1' => '1',
            'int_2' => '',
            'int_3' => '-100',
            'int_4' => '2',
            'intplus_1' => '1',
            'intplus_2' => '',
            'intplus_3' => '2',
            'nested' => [
                'input_1' => 'aDefault',
                'input_2' => '',
            ],
            'offset_1' => 'x,y',
            'offset_2' => 'x',
            'offset_3' => ',y',
            'offset_4' => '',
            'options_1' => 'default',
            'options_2' => 'option_2',
            'options_3' => '',
            'predefined' => [
                'boolean_1' => '1',
                'int_1' => '42',
            ],
            'small_1' => 'value',
            'small_2' => '',
            'string_1' => 'value',
            'string_2' => '',
            'user_1' => '0',
            'wrap_1' => 'value',
            'wrap_2' => '',
            'zeroorder_input_1' => 'value',
            'zeroorder_input_2' => '',
            'zeroorder_input_3' => '',
        ],
    ],
    'FE' => [
        'cacheHash' => [
            'enforceValidation' => true,
        ],
        'debug' => true,
        'disableNoCacheParameter' => true,
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
    ],
    'GFX' => [
        'processor' => 'GraphicsMagick',
        'processor_effects' => false,
        'processor_enabled' => true,
        'processor_path' => '/usr/bin/',
    ],
    'LOG' => [
        'TYPO3' => [
            'CMS' => [
                'deprecations' => [
                    'writerConfiguration' => [
                        'notice' => [
                            'TYPO3\CMS\Core\Log\Writer\FileWriter' => [
                                'disabled' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'MAIL' => [
        'transport' => 'sendmail',
        'transport_sendmail_command' => '/usr/local/bin/mailpit sendmail -t --smtp-addr 127.0.0.1:1025',
        'transport_smtp_encrypt' => '',
        'transport_smtp_password' => '',
        'transport_smtp_server' => '',
        'transport_smtp_username' => '',
    ],
    'SYS' => [
        'UTF8filesystem' => true,
        'caching' => [
            'cacheConfigurations' => [
                'hash' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                ],
                'pages' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
                'rootline' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
            ],
        ],
        'devIPmask' => '*',
        'displayErrors' => 1,
        'encryptionKey' => '6b14153697a082a99986a1bf556364af8472a424aa2810d31e24dfd9da34824ab1ae2ac328f9df44295d4f8ec03957ee',
        'exceptionalErrors' => 12290,
        'features' => [
            'frontend.cache.autoTagging' => true,
        ],
        'sitename' => 'New TYPO3 site',
    ],
];
