<?php

/*
 * Docs Generator
 * https://github.com/ivopetkov/docs-generator
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

$classes = array(
    'IvoPetkov\DocsGenerator' => 'src/DocsGenerator.php',
    'IvoPetkov\DocsGenerator\ClassParser' => 'src/DocsGenerator/ClassParser.php'
);

spl_autoload_register(function ($class) use ($classes) {
    if (isset($classes[$class])) {
        require __DIR__ . '/' . $classes[$class];
    }
}, true);
