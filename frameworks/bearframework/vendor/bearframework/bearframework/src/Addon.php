<?php

/*
 * Bear Framework
 * http://bearframework.com
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework;

/**
 * @property-read string $id The id of the addon.
 * @property-read string $dir The directory where the addon files are located.
 * @property-read array $options The addon options. Available values:
 *     - require - An array containing the ids of addons that must be added before this one.
 */
class Addon
{

    use \IvoPetkov\DataObjectTrait;
    use \IvoPetkov\DataObjectToArrayTrait;
    use \IvoPetkov\DataObjectToJSONTrait;

    /**
     * 
     * @param string $id
     * @param string $dir
     * @param array $options
     */
    function __construct(string $id, string $dir, array $options)
    {
        $this->defineProperty('id', [
            'type' => 'string',
            'get' => function() use ($id) {
                return $id;
            },
            'readonly' => true
        ]);
        $this->defineProperty('dir', [
            'type' => 'string',
            'get' => function() use ($dir) {
                return $dir;
            },
            'readonly' => true
        ]);
        $this->defineProperty('options', [
            'type' => 'array',
            'get' => function() use ($options) {
                return $options;
            },
            'readonly' => true
        ]);
    }

}
