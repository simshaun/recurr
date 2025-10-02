<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/translations',
    ])
    ->withPhpSets()
    ->withAttributesSets(all: true)
    ->withComposerBased(phpunit: true)
    ->withPreparedSets(codeQuality: true, typeDeclarations: true)
    ->withDeadCodeLevel(0);
