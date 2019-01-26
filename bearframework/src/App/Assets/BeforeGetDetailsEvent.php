<?php

/*
 * Bear Framework
 * http://bearframework.com
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework\App\Assets;

/**
 * @property string $filename
 * @property array $list
 * @property ?array $returnValue
 */
class BeforeGetDetailsEvent extends \BearFramework\App\Event
{

    /**
     * 
     * @param string $filename
     * @param array $list
     */
    public function __construct(string $filename, array $list)
    {
        parent::__construct('beforeGetDetails');
        $this
                ->defineProperty('filename', [
                    'type' => 'string'
                ])
                ->defineProperty('list', [
                    'type' => 'array'
                ])
                ->defineProperty('returnValue', [
                    'type' => '?array'
                ])
        ;
        $this->filename = $filename;
        $this->list = $list;
    }

}
