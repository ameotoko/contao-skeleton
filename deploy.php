<?php

namespace Deployer;

use Symfony\Component\Console\Input\InputOption;

import('recipe/contao.php');

host('example')
    ->setHostname('example.org')
    ->setRemoteUser('root')
    ->setDeployPath('/www/htdocs')
    ->set('bin/php', '/usr/bin/php')
    ->set('bin/composer', '/usr/bin/php /usr/bin/composer')
;

// Remove unneeded entries added by Contao recipe
set('shared_dirs', array_diff(get('shared_dirs'), ['contao-manager']));
set('shared_files', array_diff(get('shared_files'), ['config/parameters.yml']));

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
        upload($src, '{{release_path}}/', [
            'options' => ['--recursive', '--relative'],
            'progress_bar' => false
        ]);
    }
});

after('deploy:failed', 'deploy:unlock');

// Database helpers
set('rsync_src', __DIR__);
set('rsync_dest', get('{{release_path'));

// Restore local database with remote one
option('no-migrate', null, InputOption::VALUE_NONE, 'Skip contao:migrate step');
task('database:retrieve', static function () {
    if (!askConfirmation('Local database will be overridden. OK?')) {
        throw error('Task aborted');
    }

    echo 'Preparing backup on remote..';

    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone('UTC'));

    $filename = sprintf('backup__%s.sql.gz', $now->format('YmdHis'));

    run("cd {{release_or_current_path}} && {{bin/console}} contao:backup:create --ignore-tables=tl_log,tl_undo $filename");

    echo ".finished\n";

    runLocally('mkdir -p var/backups');

    echo 'Downloading backup archive..';
    download("{{release_or_current_path}}/var/backups/$filename", 'var/backups/');

    echo ".finished\n";
    echo 'Restoring local database....';
    runLocally("symfony php vendor/bin/contao-console contao:backup:restore $filename");
    echo ".finished\n";
    echo 'Run migration scripts.......';
    try {
        if (input()->getOption('no-migrate')) {
            throw new \Exception();
        }

        runLocally('symfony php vendor/bin/contao-console contao:migrate --no-interaction');
        echo ".finished\n";
    } catch (\Exception $e) {
        echo ".skipped\n";
    }

    echo "  Restore of local database completed\n";
})->desc('Downloads a database dump from given host and overrides the local database.');

// Restore remote database with local one
task('database:release', static function () {
    if (!askConfirmation('Remote (!) database will be overridden. OK?')) {
        throw error('Task aborted');
    }

    echo 'Preparing local backup.....';

    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone('UTC'));

    $filename = sprintf('backup__%s.sql.gz', $now->format('YmdHis'));

    runLocally("symfony php vendor/bin/contao-console contao:backup:create $filename");

    echo ".finished\n";
    echo 'Uploading backup archive...';
    upload("var/backups/$filename", '{{release_or_current_path}}/var/backups/');

    echo ".finished\n";
    echo 'Restoring remote database..';
    run("cd {{release_or_current_path}} && {{bin/console}} contao:backup:restore $filename");
    echo ".finished\n";

    echo "  Restore of remote database completed\n";
})->desc('Restores the local database on the given host.');

after('database:release', 'contao:migrate');
