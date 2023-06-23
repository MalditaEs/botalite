<?php

namespace Deployer;

use Symfony\Component\Dotenv\Dotenv;

require 'recipe/symfony.php';
require 'contrib/cachetool.php';

// Project name
set('application', 'Botalite Web');

// Project repository
set('repository', 'git@github.com:MalditaEs/botalite.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

set('bin/console', function () {
    return parse('{{bin/php}} {{release_path}}/bin/console --no-interaction');
});

set('keep_releases', 2);

// Shared files/dirs between deploys
add('shared_files', [
    '.env.local',
    '.env',
]);

add('shared_dirs',
    [
    ]);

// Writable dirs by web server
add('writable_dirs', []);

// Hosts

host('botalite@85.90.247.211')
    ->set('deploy_path', '/home/botalite/app')
    ->set('labels', ['roles' => ['db', 'cache', 'workers']])
    ->set('cachetool', '127.0.0.1:9081');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

//after('deploy:update_code', 'yarn:install');

task('load:assets', function () {
//    run('cd {{release_path}} && {{bin/console}} fos:js-routing:dump --format=json --target=public/js/fos_js_routes.json');
    run('cd {{release_path}} && {{bin/console}} assets:install public');
    run('cd {{release_path}} && yarn install --force --ignore-optional && yarn encore production');
});

after('deploy:vendors', 'load:assets');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
task('database:migrate')->select('roles=db');

//before('deploy:symlink', 'database:migrate');

task( 'load:env-vars', function () {
    $environment = run( 'cat {{deploy_path}}/..env.local' );
    $dotenv      = new Dotenv();
    $data        = $dotenv->parse( $environment );
    set( 'env', $data );
} );

task('deploy:stop-workers', function () {
    run('cd {{release_path}} && {{bin/console}} messenger:stop-workers');
})->select('roles=cache');

//task('deploy:generate-api-keys-if-needed', function () {
//    run('cd {{release_path}} && {{bin/console}} lexik:jwt:generate-keypair --skip-if-exists');
//})->select('roles=db');

//after('deploy', 'deploy:generate-api-keys-if-needed');
after('deploy:symlink', 'cachetool:clear:opcache');
after('cachetool:clear:opcache', 'deploy:stop-workers');
