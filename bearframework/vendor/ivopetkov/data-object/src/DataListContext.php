<?php

/*
 * Data Object
 * https://github.com/ivopetkov/data-object
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 * Information about the operations applied on the data list.
 */
class DataListContext
{

    /**
     *
     * @var array 
     */
    public $filterByProperties = [];

    /**
     *
     * @var array 
     */
    public $sortByProperties = [];

    /**
     *
     * @var array 
     */
    public $requestedProperties = [];

}
