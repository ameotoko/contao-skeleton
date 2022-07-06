<?php

namespace Deployer;

import('recipe/contao.php');

host('example')
    ->setHostname('example.org')
    ->setRemoteUser('root')
    ->setDeployPath('/www/htdocs')
    ->set('bin/php', '/usr/bin/php')
    ->set('bin/composer', '/usr/bin/php /usr/bin/composer')
;

// Uncomment, if this step is not needed in your host environment
// task('deploy:writable', function () {});

// Uncomment to disable releases
// import(__DIR__ . '/deploy-norelease.php');

// Upload the project code
desc('Upload project files');
task('deploy:update_code', function () {
    // adjust entries here
    foreach([
        'config',
        'public/build',
        'src',
        'translations',
        '.env',
        'composer.json',
        'composer.lock',
    ] as $src) {
        upload($src, '{{release_path}}/', ['options' => ['--recursive', '--relative']]);
    }
});

after('deploy:failed', 'deploy:unlock');

// Database helpers
set('rsync_src', __DIR__);
set('rsync_dest', get('{{release_path'));

// Restore local database with remote one
task('database:retrieve', static function () {
    echo 'Preparing backup on remote..';

    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone('UTC'));

    $filename = sprintf('backup__%s.sql.gz', $now->format('YmdHis'));

    run("cd {{release_path}} && {{bin/php}} {{bin/console}} contao:backup:create $filename");

    echo ".finished\n";

    runLocally('mkdir -p var/backups');

    echo 'Downloading backup archive..';
    download("{{release_path}}/var/backups/$filename", 'var/backups/');

    echo ".finished\n";
    echo 'Restoring local database....';
    runLocally("symfony php vendor/bin/contao-console contao:backup:restore $filename");
    echo ".finished\n";
    echo 'Run migration scripts.......';
    try {
        runLocally('symfony php vendor/bin/contao-console contao:migrate --no-interaction');
        echo ".finished\n";
    } catch (\Exception $e) {
        echo ".skipped\n";
    }

    echo "  Restore of local database completed\n";
})->desc('Downloads a database dump from given host and overrides the local database.');

task('ask_retrieve', static function () {
    if (!askConfirmation('Local database will be overriden. OK?')) {
        die("Restore cancelled.\n");
    }
});

before('database:retrieve', 'ask_retrieve');

// Restore remote database with local one
task('database:release', static function () {
    echo 'Preparing local backup.....';

    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone('UTC'));

    $filename = sprintf('backup__%s.sql.gz', $now->format('YmdHis'));

    runLocally("symfony php vendor/bin/contao-console contao:backup:create $filename");

    echo ".finished\n";
    echo 'Uploading backup archive...';
    upload("var/backups/$filename", '{{release_path}}/var/backups/');

    echo ".finished\n";
    echo 'Restoring remote database..';
    run("cd {{release_path}} && {{bin/php}} {{bin/console}} contao:backup:restore $filename");
    echo ".finished\n";

    echo "  Restore of remote database completed\n";
})->desc('Restores the local database on the given host.');

task('ask_release', static function () {
    if (!askConfirmation('Remote (!) database will be overriden. OK?')) {
        die("Restore cancelled.\n");
    }
});

before('database:release', 'ask_release');
after('database:release', 'contao:migrate');
