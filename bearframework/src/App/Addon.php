<?php

/*
 * Bear Framework
 * http://bearframework.com
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework\App;

/**
 * @property-read string $id The id of the addon.
 * @property-read string $dir The directory where the addon files are located.
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
     */
    public function __construct(string $id, string $dir)
    {
        $this
                ->defineProperty('id', [
                    'type' => 'string',
                    'get' => function() use ($id) {
                        return $id;
                    },
                    'readonly' => true
                ])
                ->defineProperty('dir', [
                    'type' => 'string',
                    'get' => function() use ($dir) {
                        return $dir;
                    },
                    'readonly' => true
        ]);
    }

}
