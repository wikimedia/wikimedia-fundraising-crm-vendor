<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;

return RectorConfig::configure()
  ->withPaths([
    __DIR__ . '/src',
    __DIR__ . '/tests',
  ])
  ->withPreparedSets(typeDeclarationDocblocks: true)
  ->withRules([
    RemoveUselessParamTagRector::class,
    RemoveUselessReturnTagRector::class,
    RemoveUselessVarTagRector::class,
  ]);
