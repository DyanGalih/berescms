<?php

// Autoload composer
require_once __DIR__ . '/vendor/autoload.php';

// Register global aliases defined in modules/system/aliases.php
$aliases = require __DIR__ . '/modules/system/aliases.php';
foreach ($aliases as $alias => $class) {
    if (!class_exists($alias, false) && !interface_exists($alias, false) && !trait_exists($alias, false)) {
        if (class_exists($class) || interface_exists($class) || trait_exists($class)) {
            class_alias($class, $alias);
        }
    }
}
