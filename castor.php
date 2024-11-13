<?php

declare(strict_types=1);

use Castor\Attribute\AsTask;

use function Castor\run;

// === Build Commands ===

#[AsTask(description: 'Build assets with Webpack')]
function buildWebpack(): void
{
    run('pnpm run build');
}

#[AsTask(description: 'Watch assets for changes and rebuild with Webpack')]
function watchWebpack(): void
{
    run('pnpm run dev --watch');
}

// === Development Commands ===

#[AsTask(description: 'Install PHP and JS dependencies')]
function install(): void
{
    run('composer install');
    run('composer install --working-dir=tools/php-cs-fixer');
    run('pnpm install');
}

#[AsTask(description: 'Update PHP and JS dependencies')]
function update(): void
{
    run('composer update');
    run('composer update --working-dir=tools/php-cs-fixer');
}

#[AsTask(description: 'Debug Symfony Twig components')]
function debugTwigComponent(): void
{
    run('php bin/console debug:twig-component');
}

#[AsTask(description: 'Prepare for production: clear cache, build assets, and run server')]
function simulateProduction(): void
{
    ccProd();
    buildWebpack();
    runServer();
}

// === Symfony Cache and Debug Commands ===

#[AsTask(description: 'Clear Symfony cache')]
function cc(): void
{
    run('php bin/console cache:clear');
}

#[AsTask(description: 'Clear Symfony cache for production')]
function ccProd(): void
{
    run('php bin/console cache:clear --env=prod');
}

// === Symfony Server Commands ===

#[AsTask(description: 'Start Symfony server')]
function runServer(): void
{
    run('symfony server:start');
}

#[AsTask(description: 'Start Symfony server without TLS')]
function runNoTls(): void
{
    run('symfony server:start --no-tls -d');
}

#[AsTask(description: 'Stop Symfony server')]
function stopServer(): void
{
    run('symfony server:stop');
}

// === Routing and Migration Commands ===

#[AsTask(description: 'List all routes with controllers')]
function routes(): void
{
    run('php bin/console debug:router --show-controllers');
}

#[AsTask(description: 'Generate a new database migration')]
function migration(): void
{
    run('php bin/console make:migration');
}

#[AsTask(description: 'Execute pending migrations')]
function migrate(): void
{
    run('php bin/console doctrine:migrations:migrate');
}

#[AsTask(description: 'Generate migration diff for cleaning')]
function migrationClean(): void
{
    run('php bin/console doctrine:migrations:diff');
}

#[AsTask(description: 'Run all migrations without prompts')]
function migrateAll(): void
{
    run('php bin/console doctrine:migrations:migrate --no-interaction');
}

#[AsTask(description: 'Force-run migrations without prompts')]
function migrateF(): void
{
    run('php bin/console doctrine:migrations:migrate --no-interaction');
}

#[AsTask(description: 'Rollback the last migration')]
function migratePrev(): void
{
    run('php bin/console doctrine:migrations:migrate prev');
}

// === Symfony Code Generation Commands ===

#[AsTask(description: 'Create a new controller')]
function controller(): void
{
    run('php bin/console make:controller');
}

#[AsTask(description: 'Create a new entity')]
function entity(): void
{
    run('php bin/console make:entity');
}

#[AsTask(description: 'Create a new form')]
function form(): void
{
    run('php bin/console make:form');
}

#[AsTask(description: 'Update Symfony recipes')]
function recipesUpdate(): void
{
    run('composer recipes:update');
}

// === Code Quality and Testing Commands ===

#[AsTask(description: 'Run unit tests with detailed output')]
function test(): void
{
    run('php bin/phpunit --testdox --debug');
}

#[AsTask(description: 'Run PHPStan static analysis')]
function stan(): void
{
    run('vendor/bin/phpstan analyse -c phpstan.neon --memory-limit 1G');
}

#[AsTask(description: 'Lint PHP code (dry-run)')]
function lintPhp(): void
{
    run('php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --config=.php-cs-fixer.dist.php');
}

#[AsTask(description: 'Auto-fix PHP code style')]
function fixPhp(): void
{
    run('php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php');
}

#[AsTask(description: 'Lint JavaScript code')]
function lintJs(): void
{
    run('pnpm eslint --config eslint.config.cjs');
}

#[AsTask(description: 'Auto-fix JavaScript code')]
function fixJs(): void
{
    run('pnpm eslint --config eslint.config.cjs --fix');
}

#[AsTask(description: 'Lint Twig templates')]
function lintTwig(): void
{
    run('php vendor/bin/twig-cs-fixer');
}

#[AsTask(description: 'Auto-fix Twig templates')]
function fixTwig(): void
{
    run('php vendor/bin/twig-cs-fixer --fix');
}

#[AsTask(description: 'Preview Rector fixes (dry-run)')]
function rector(): void
{
    run('php vendor/bin/rector process --dry-run -c ./rector.php');
}

#[AsTask(description: 'Apply Rector fixes')]
function rectorFix(): void
{
    run('php vendor/bin/rector process -c ./rector.php');
}

// === Helper Tasks ===

#[AsTask(description: 'Prepare code by fixing style issues and running tests')]
function prepare(): void
{
    fixJs();
    fixTwig();
    rectorFix();
    fixPhp();
    stan();
    test();
}

#[AsTask(description: 'Fix code style issues (PHP/JS/Twig)')]
function fix(): void
{
    fixJs();
    fixTwig();
    rectorFix();
    fixPhp();
}
