<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/config', __DIR__.'/src', __DIR__.'/tests'])
    ->withImportNames(removeUnusedImports: true)
    ->withPhpSets(php83: true)
    ->withPreparedSets(
        codeQuality: true,
        codingStyle: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true
    )
    ->withTypeCoverageLevel(36)
    ->withDeadCodeLevel(40)
    ->withSets([
        SymfonySetList::SYMFONY_71,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
    ])
    ->withPHPStanConfigs([__DIR__.'/phpstan.dist.neon'])
    ->withCache('./var/cache/rector', FileCacheStorage::class);
