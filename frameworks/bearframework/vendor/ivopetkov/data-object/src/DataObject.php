<?php

/*
 * Data Object
 * https://github.com/ivopetkov/data-object
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 * A data object that supports registering properties and importing/exporting from array and JSON.
 */
class DataObject implements \ArrayAccess
{

    use DataObjectTrait;
    use DataObjectArrayAccessTrait;
    use DataObjectToArrayTrait;
    use DataObjectFromArrayTrait;
    use DataObjectToJSONTrait;
    use DataObjectFromJSONTrait;

    /**
     * Constructs a new data object.
     * 
     * @param array $data The data to use for the properties values.
     */
    public function __construct(array $data = [])
    {
        $this->initialize();
        foreach ($data as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * 
     */
    protected function initialize()
    {
        
    }

}
