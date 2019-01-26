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
trait DataObjectTrait
{

    /**
     * An array containing the registered properties and the data set.
     * 
     * @var array 
     */
    private $internalDataObjectData = [];

    /**
     * Defines a new property.
     * 
     * @param string $name The property name.
     * @param array $options The property options. Available values: 
     *   init (callable)
     *   get (callable)
     *   set (callable)
     *   unset (callable)
     *   readonly (boolean)
     *   type (string)
     *   encodeInJSON (boolean) - Base64 encode the value of the property when it's json encoded (in toJSON() for example). The default value is FALSE.
     * @throws \InvalidArgumentException
     * @return object Returns a reference to the object.
     */
    protected function defineProperty(string $name, array $options = [])
    {
        $data = [];
        if (isset($options['init'])) {
            if (!is_callable($options['init'])) {
                throw new \InvalidArgumentException('The \'init\' option must be of type callable, ' . gettype($options['init']) . ' given');
            }
            $data[1] = $options['init'];
        }
        if (isset($options['get'])) {
            if (!is_callable($options['get'])) {
                throw new \InvalidArgumentException('The \'get\' option must be of type callable, ' . gettype($options['get']) . ' given');
            }
            $data[2] = $options['get'];
        }
        if (isset($options['set'])) {
            if (!is_callable($options['set'])) {
                throw new \InvalidArgumentException('The \'set\' option must be of type callable, ' . gettype($options['set']) . ' given');
            }
            $data[3] = $options['set'];
        }
        if (isset($options['unset'])) {
            if (!is_callable($options['unset'])) {
                throw new \InvalidArgumentException('The \'unset\' option must be of type callable, ' . gettype($options['unset']) . ' given');
            }
            $data[4] = $options['unset'];
        }
        if (isset($options['readonly'])) {
            if (!is_bool($options['readonly'])) {
                throw new \InvalidArgumentException('The \'readonly\' option must be of type bool, ' . gettype($options['readonly']) . ' given');
            }
            if ($options['readonly']) {
                $data[5] = true;
            }
        }
        if (isset($options['type'])) {
            if (!is_string($options['type'])) {
                throw new \InvalidArgumentException('The \'type\' option must be of type string, ' . gettype($options['type']) . ' given');
            }
            $type = $data[6] = $options['type'];
            if ($type{0} !== '?') {
                if (isset($data[1]) || isset($data[2], $data[4])) {
                    // has init or get and unset callbacks
                } elseif ($type === 'array') {
                    $data[0] = function() {
                        return [];
                    };
                } elseif ($type === 'float') {
                    $data[0] = function() {
                        return 0.0;
                    };
                } elseif ($type === 'int') {
                    $data[0] = function() {
                        return 0;
                    };
                } elseif ($type === 'string') {
                    $data[0] = function() {
                        return '';
                    };
                } else {
                    $data[0] = function() use ($type, $name) {
                        if (class_exists($type)) {
                            return new $type();
                        } else {
                            throw new \InvalidArgumentException('Cannot find a class named \'' . $type . '\' for the default value of \'' . $name . '\'.');
                        }
                    };
                }
            }
        }
        if (isset($options['encodeInJSON'])) {
            if (!is_bool($options['encodeInJSON'])) {
                throw new \InvalidArgumentException('The \'encodeInJSON\' option must be of type bool, ' . gettype($options['encodeInJSON']) . ' given');
            }
            if ($options['encodeInJSON']) {
                $data[7] = true;
            }
        }
        $this->internalDataObjectData['p' . $name] = $data;
        return $this;
    }

    /**
     * 
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function &__get($name)
    {
        if (isset($this->internalDataObjectData['p' . $name])) {
            if (isset($this->internalDataObjectData['p' . $name][2])) { // get exists
                $value = call_user_func($this->internalDataObjectData['p' . $name][2]);
                return $value;
            }
            if (array_key_exists('d' . $name, $this->internalDataObjectData)) {
                return $this->internalDataObjectData['d' . $name];
            }
            if (isset($this->internalDataObjectData['p' . $name][1])) { // init exists
                $this->internalDataObjectData['d' . $name] = call_user_func($this->internalDataObjectData['p' . $name][1]);
                return $this->internalDataObjectData['d' . $name];
            }
            if (isset($this->internalDataObjectData['p' . $name][0])) { // default init exists
                $this->internalDataObjectData['d' . $name] = call_user_func($this->internalDataObjectData['p' . $name][0]);
                return $this->internalDataObjectData['d' . $name];
            }
            $value = null;
            return $value;
        }
        if (array_key_exists('d' . $name, $this->internalDataObjectData)) {
            return $this->internalDataObjectData['d' . $name];
        }
        throw new \Exception('Undefined property: ' . get_class($this) . '::$' . $name);
    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
    public function __set($name, $value): void
    {
        if (isset($this->internalDataObjectData['p' . $name])) {
            if (isset($this->internalDataObjectData['p' . $name][5])) { // readonly
                throw new \Exception('The property ' . get_class($this) . '::$' . $name . ' is readonly');
            }
            if (isset($this->internalDataObjectData['p' . $name][6])) { // type exists
                $type = $this->internalDataObjectData['p' . $name][6];
                $nullable = false;
                $ok = false;
                if ($type[0] === '?') {
                    if ($value === null) {
                        $ok = true;
                    }
                    $type = substr($type, 1);
                    $nullable = true;
                }
                if (!$ok) {
                    switch ($type) {
                        case 'array':
                            $ok = is_array($value);
                            break;
                        case 'callable':
                            $ok = is_callable($value);
                            break;
                        case 'bool':
                            $ok = is_bool($value);
                            break;
                        case 'float':
                            $ok = is_float($value);
                            break;
                        case 'int':
                            $ok = is_int($value);
                            break;
                        case 'string':
                            $ok = is_string($value);
                            break;
                    }
                }
                if (!$ok) {
                    $ok = class_exists($type) && is_a($value, $type);
                }
                if (!$ok) {
                    $valueType = gettype($value);
                    if (array_search($type, ['array', 'callable', 'bool', 'float', 'int', 'string']) === false) {
                        if ($valueType === 'object') {
                            throw new \Exception('The value of \'' . $name . '\' must be an instance of ' . $type . ($nullable ? ' or null' : '') . ', instance of ' . get_class($value) . ' given');
                        } else {
                            throw new \Exception('The value of \'' . $name . '\' must be an instance of ' . $type . ($nullable ? ' or null' : '') . ', ' . $valueType . ' given');
                        }
                    } else {
                        throw new \Exception('The value of \'' . $name . '\' must be of type ' . $type . ($nullable ? ' or null' : '') . ', ' . $valueType . ' given');
                    }
                }
            }
            if (isset($this->internalDataObjectData['p' . $name][3])) { // set exists
                $this->internalDataObjectData['d' . $name] = call_user_func($this->internalDataObjectData['p' . $name][3], $value);
                if ($this->internalDataObjectData['d' . $name] === null) {
                    unset($this->internalDataObjectData['d' . $name]);
                }
                return;
            }
        }
        $this->internalDataObjectData['d' . $name] = $value;
    }

    /**
     * 
     * @param string $name
     * @return boolean
     */
    public function __isset($name): bool
    {
        if (isset($this->internalDataObjectData['p' . $name])) {
            return $this->$name !== null;
        }
        return isset($this->internalDataObjectData['d' . $name]);
    }

    /**
     * 
     * @param string $name
     */
    public function __unset($name): void
    {
        if (isset($this->internalDataObjectData['p' . $name])) {
            if (isset($this->internalDataObjectData['p' . $name][5])) { // readonly
                throw new \Exception('The property ' . get_class($this) . '::$' . $name . ' is readonly');
            }
            if (isset($this->internalDataObjectData['p' . $name][4])) { // unset exists
                $this->internalDataObjectData['d' . $name] = call_user_func($this->internalDataObjectData['p' . $name][4]);
                return;
            }
        }
        if (array_key_exists('d' . $name, $this->internalDataObjectData)) {
            unset($this->internalDataObjectData['d' . $name]);
        }
    }

}
