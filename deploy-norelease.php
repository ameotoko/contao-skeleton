<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Deployer;

desc('Preparing host for deploy without releases');
task('deploy:setup', function () {
    run('if [ ! -d {{deploy_path}} ]; then mkdir -p {{deploy_path}}; fi');
    run('cd {{deploy_path}} && if [ ! -d .dep ]; then mkdir .dep; fi');
});

set('release_path', function () {
    return get('deploy_path');
});

set('release_or_current_path', function () {
    return get('deploy_path');
});

// These tasks are not necessary without releases
task('deploy:release', function () {});
task('deploy:shared', function () {});
task('deploy:symlink', function () {});
task('deploy:cleanup', function () {});
task('rollback', function () {});
