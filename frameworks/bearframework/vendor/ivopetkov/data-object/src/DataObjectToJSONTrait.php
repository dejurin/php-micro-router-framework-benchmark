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
trait DataObjectToJSONTrait
{

    /**
     * Returns the object data converted as JSON.
     * 
     * @return string The object data converted as JSON.
     * @throws \Exception
     */
    public function toJSON(): string
    {
        // Copied to DataList. Copy there when the function is modified !!!
        $toJSON = function($object): string {
            $result = [];

            $vars = get_object_vars($object);
            foreach ($vars as $name => $value) {
                if ($name !== 'internalDataObjectData') {
                    $reflectionProperty = new \ReflectionProperty($object, $name);
                    if ($reflectionProperty->isPublic()) {
                        $result[$name] = null;
                    }
                }
            }
            $propertiesToEncode = [];
            if (isset($object->internalDataObjectData)) {
                foreach ($object->internalDataObjectData as $name => $value) {
                    $result[substr($name, 1)] = null;
                    if (substr($name, 0, 1) === 'p' && isset($value[7])) { // encodeInJSON is set
                        $propertiesToEncode[substr($name, 1)] = true;
                    }
                }
            }
            ksort($result);
            foreach ($result as $name => $null) {
                $value = $object instanceof \ArrayAccess ? $object[$name] : (isset($object->$name) ? $object->$name : null);
                if (method_exists($value, 'toJSON')) {
                    $result[$name] = $value->toJSON();
                } else {
                    if ($value instanceof \DateTime) {
                        $value = $value->format('c');
                    }
                    if (isset($propertiesToEncode[$name]) && $value !== null) {
                        if (is_string($value)) {
                            $value = 'data:;base64,' . base64_encode($value);
                        } else {
                            throw new \Exception('The value of the ' . $name . ' property cannot be JSON encoded. It must be of type string!');
                        }
                    }
                    $result[$name] = json_encode($value);
                    if ($result[$name] === false) {
                        throw new \Exception('Invalid characters in ' . $name . '! Cannot JSON encode the value: ' . print_r($value, true));
                    }
                }
            }
            $json = '';
            foreach ($result as $name => $value) {
                $json .= '"' . addcslashes($name, '"\\') . '":' . $value . ',';
            }
            $json = '{' . rtrim($json, ',') . '}';
            return $json;
        };
        return $toJSON($this);
    }

}
