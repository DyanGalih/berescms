<?php

// Autoload composer
require_once __DIR__ . '/vendor/autoload.php';

// Register module-aware ClassLoader for case-sensitive filesystem resolution
$loader = new \Winter\Storm\Support\ClassLoader(
    new \Winter\Storm\Filesystem\Filesystem(),
    __DIR__,
    sys_get_temp_dir() . '/phpstan_classes.php'
);
$loader->manifest = [];
$loader->register();
$loader->autoloadPackage('Backend\\', 'modules/backend/');
$loader->autoloadPackage('System\\', 'modules/system/');
$loader->autoloadPackage('Cms\\', 'modules/cms/');

// Register global aliases defined in modules/system/aliases.php
$aliases = require __DIR__ . '/modules/system/aliases.php';
foreach ($aliases as $alias => $class) {
    if (!class_exists($alias, false) && !interface_exists($alias, false) && !trait_exists($alias, false)) {
        if (class_exists($class) || interface_exists($class) || trait_exists($class)) {
            class_alias($class, $alias);
        }
    }
}
