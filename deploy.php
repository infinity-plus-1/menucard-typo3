<?php

declare(strict_types=1);

namespace Deployer;

// Include base recipes
require 'recipe/common.php';
require 'contrib/cachetool.php';
require 'contrib/rsync.php';

// Include hosts
import('.hosts.yml');

set('http_user', 'www-data');
set('http_group', 'www-data');

set('/usr/local/bin/php', 'php');
set('bin/typo3', '{{release_path}}/vendor/bin/typo3');

// Set maximum number of releases
set('keep_releases', 5);

// Set TYPO3 docroot
set('typo3_webroot', 'public');

// Set shared directories
$sharedDirectories = [
    '{{typo3_webroot}}/fileadmin',
    '{{typo3_webroot}}/typo3temp',
    'config',
    'var',
];
set('shared_dirs', $sharedDirectories);

// Set shared files
$sharedFiles = [
    '{{typo3_webroot}}/.htaccess',
];
set('shared_files', $sharedFiles);

// Set writable directories
set('writable_dirs', [
    'config',
    '{{typo3_webroot}}/fileadmin',
    '{{typo3_webroot}}/typo3temp',
    '{{typo3_webroot}}/typo3conf',
    '{{typo3_webroot}}/uploads',
    
]);

// Define all rsync excludes
$exclude = [
    // OS specific files
    '.DS_Store',
    'Thumbs.db',
    // Project specific files and directories
    '.ddev',
    '.editorconfig',
    '.fleet',
    '.git*',
    '.idea',
    '.php-cs-fixer.dist.php',
    '.vscode',
    'auth.json',
    'deploy.php',
    '.hosts.yml',
    'phpstan.neon',
    'phpunit.xml',
    'README*',
    'rector.php',
    'typoscript-lint.yml',
    '/.deployment',
    '/var',
    '/**/Tests/*',
];

// Define rsync options
set('rsync', [
    'exclude' => array_merge($sharedDirectories, $sharedFiles, $exclude),
    'exclude-file' => false,
    'include' => [],
    'include-file' => false,
    'filter' => [],
    'filter-file' => false,
    'filter-perdir' => false,
    'flags' => 'az',
    'options' => ['delete'],
    'timeout' => 300,
]);
set('rsync_src', './');

// Use rsync to update code during deployment
task('deploy:update_code', function () {
    invoke('rsync:warmup');
    invoke('rsync');
});

// TYPO3 tasks
desc('Flush all caches');
task('typo3:cache_flush', function () {
    run('{{bin/typo3}} cache:flush');
});

desc('Warm up caches');
task('typo3:cache_warmup', function () {
    run('{{bin/typo3}} cache:warmup');
});

desc('Set up all installed extensions');
task('typo3:extension_setup', function () {
    run('{{bin/typo3}} extension:setup');
});

desc('Fix folder structure');
task('typo3:fix_folder_structure', function () {
    run('{{bin/typo3}} install:fixfolderstructure');
});

desc('Update language files');
task('typo3:language_update', function () {
    run('{{bin/typo3}} language:update');
});

desc('Update reference index');
task('typo3:update_reference_index', function () {
    run("{{bin/typo3}} referenceindex:update");
});

desc('Execute upgrade wizards');
task('typo3:upgrade_all', function () {
    run('{{bin/typo3}} upgrade:prepare');
    run('{{bin/typo3}} upgrade:run all --confirm all');
});

// Register TYPO3 tasks
before('deploy:symlink', function () {
    //invoke('typo3:fix_folder_structure');
    invoke('typo3:language_update');
});
after('deploy:symlink', function () {
    invoke('typo3:extension_setup');
    invoke('typo3:update_reference_index');
    //invoke('typo3:upgrade_all');
    invoke('typo3:cache_flush');
    invoke('typo3:cache_warmup');
});

//Cleanup task
desc('Cleanup old releases');
task('cleanup', function () {
    run("cd {{deploy_path}} && if [ -d releases ]; then ls -dt releases/* | tail -n +{{keep_releases}} | xargs -r rm -rf; fi");
});

// Main deployment task
desc('Deploy TYPO3 project');
task('deploy', [
    'deploy:prepare',
    'deploy:update_code',        // Code via rsync aktualisieren
    'deploy:shared',             // Shared files/dirs verlinken
    'deploy:vendors',            // composer install (vendor Ordner)
    'deploy:writable',           // Schreibrechte setzen
    'deploy:symlink',            // Symlink auf neues Release setzen
    'cleanup',                   // Alte Releases entfernen
]);

// Unlock on failed deployment
after('deploy:failed', 'deploy:unlock');
