<?php

/*
 * Data Object
 * https://github.com/ivopetkov/data-object
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 * 
 */
trait DataObjectArrayAccessTrait
{

    /**
     * 
     * @param string $offset
     * @return mixed
     */
    public function &offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * 
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset !== null) {
            $this->$offset = $value;
        }
    }

    /**
     * 
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->$offset);
    }

    /**
     * 
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->$offset);
    }

}
