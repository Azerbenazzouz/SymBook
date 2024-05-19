<?php
namespace Deployer;

require 'recipe/composer.php';

// Config
set('application', 'Symbook');
set('repository', 'git@github.com:Azerbenazzouz/SymBook.git');
set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-scripts');

add('shared_files', ['.env.local']);
add('shared_dirs', ['public/upload']);
add('writable_dirs', []);

// Hosts
host('localhost')
    ->setPort(22)
    ->set('branch', 'develop')
    ->set('deploy_path', '~/deploy-folder')
;
// Tasks
task('pwd', function (): void {
    $result = run('pwd');
    writeln("Current dir: {$result}");
});
// Hooks

// task('deploy:build:assets', function (): void {
//     run('yarn install');
//     run('yarn encore production');
// })->desc('Install front-end assets');
task('deploy:build:assets', function () {
    run('yarn install');
    run('yarn encore production');
})->desc('Install front-end assets');

before('deploy:symlink', 'deploy:build:assets');

// Upload assets
task('upload:assets', function (): void {
    upload(__DIR__.'/public/build/', '{{release_path}}/public/build');
});

after('deploy:build:assets', 'upload:assets');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');