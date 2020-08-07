<?php

/*
 * Bear Framework
 * http://bearframework.com
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework\App;

/**
 * A data item.
 * 
 * @property string|null $key The key of the data item.
 * @property string|null $value The value of the data item.
 * @property \IvoPetkov\DataObject $metadata The metadata of the data item.
 */
class DataItem
{

    use \IvoPetkov\DataObjectTrait;
    use \IvoPetkov\DataObjectToArrayTrait;
    use \IvoPetkov\DataObjectToJSONTrait;

    function __construct()
    {
        $this->defineProperty('key', [
            'type' => '?string'
        ]);
        $this->defineProperty('value', [
            'type' => '?string'
        ]);
        $this->defineProperty('metadata', [
            'init' => function() {
                return new \IvoPetkov\DataObject();
            },
            'readonly' => true
        ]);
    }

}
