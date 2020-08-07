<?php

/*
 * Data Object
 * https://github.com/ivopetkov/data-object
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

$classes = array(
    'IvoPetkov\DataList' => 'src/DataList.php',
    'IvoPetkov\DataListContext' => 'src/DataListContext.php',
    'IvoPetkov\DataObject' => 'src/DataObject.php',
    'IvoPetkov\DataObjectArrayAccessTrait' => 'src/DataObjectArrayAccessTrait.php',
    'IvoPetkov\DataObjectFromArrayTrait' => 'src/DataObjectFromArrayTrait.php',
    'IvoPetkov\DataObjectFromJSONTrait' => 'src/DataObjectFromJSONTrait.php',
    'IvoPetkov\DataObjectToArrayTrait' => 'src/DataObjectToArrayTrait.php',
    'IvoPetkov\DataObjectToJSONTrait' => 'src/DataObjectToJSONTrait.php',
    'IvoPetkov\DataObjectTrait' => 'src/DataObjectTrait.php'
);

spl_autoload_register(function ($class) use ($classes) {
    if (isset($classes[$class])) {
        require __DIR__ . '/' . $classes[$class];
    }
}, true);
