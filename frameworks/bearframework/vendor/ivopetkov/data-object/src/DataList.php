<?php

/*
 * Data Object
 * https://github.com/ivopetkov/data-object
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

use IvoPetkov\DataObject;
use IvoPetkov\DataListContext;

/**
 * A list of data objects that can be easily filtered, sorted, etc. The objects can be lazy loaded using a callback in the constructor.
 * 
 * @property-read int $length The number of objects in the list.
 */
class DataList implements \ArrayAccess, \Iterator
{

    /**
     * The list data objects.
     * 
     * @var array 
     */
    private $data = [];

    /**
     * The pointer when the list is iterated.
     * 
     * @var int
     */
    private $pointer = 0;

    /**
     * The list of actions (sort, filter, etc.) that must be applied to the list.
     * 
     * @var array 
     */
    private $actions = [];

    /**
     * Constructs a new data objects list.
     * 
     * @param array|iterable|callback $dataSource An array or an iterable containing objects or arrays that will be converted into data objects or a callback that returns such. The callback option enables lazy data loading.
     * @throws \InvalidArgumentException
     */
    public function __construct($dataSource = null)
    {
        if ($dataSource !== null) {
            if (is_array($dataSource) || $dataSource instanceof \Traversable) {
                foreach ($dataSource as $value) {
                    $this->data[] = $value;
                }
                return;
            }
            if (is_callable($dataSource)) {
                $this->data = $dataSource;
                return;
            }
            throw new \InvalidArgumentException('The data argument must be iterable or a callback that returns such data.');
        }
    }

    /**
     * Converts the value into object if needed.
     * 
     * @param int $index
     */
    private function updateValueIfNeeded(&$data, $index)
    {
        $value = $data[$index];
        if (is_callable($value)) {
            $value = call_user_func($value);
            $data[$index] = $value;
        }
        if (is_object($value)) {
            return $value;
        }
        $value = (object) $value;
        $data[$index] = $value;
        return $value;
    }

    /**
     * Converts all values into objects if needed.
     */
    private function updateAllValuesIfNeeded(&$data)
    {
        foreach ($data as $index => $value) {
            $this->updateValueIfNeeded($data, $index);
        }
    }

    /**
     * 
     * @param int $offset
     * @param \IvoPetkov\DataObject|null $value
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_int($offset) && $offset !== null) {
            throw new \Exception('The offset must be of type int or null');
        }
        $this->update();
        if (is_null($offset)) {
            $this->data[] = $value;
            return;
        }
        if (is_int($offset) && $offset >= 0 && (isset($this->data[$offset]) || $offset === sizeof($this->data))) {
            $this->data[$offset] = $value;
            return;
        }
        throw new \Exception('The offset is not valid.');
    }

    /**
     * 
     * @param int $offset
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function offsetExists($offset): bool
    {
        $this->update();
        return isset($this->data[$offset]);
    }

    /**
     * 
     * @param int $offset
     * @throws \InvalidArgumentException
     */
    public function offsetUnset($offset): void
    {
        $this->update();
        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
            $this->data = array_values($this->data);
        }
    }

    /**
     * 
     * @param int $offset
     * @return \IvoPetkov\DataObject|null
     * @throws \InvalidArgumentException
     */
    public function offsetGet($offset)
    {
        $this->update();
        if (isset($this->data[$offset])) {
            return $this->updateValueIfNeeded($this->data, $offset);
        }
        return null;
    }

    /**
     * Returns the object at the index specified or null if not found.
     * 
     * @param int $index The index of the item.
     * @return \IvoPetkov\DataObject|null The object at the index specified or null if not found.
     * @throws \InvalidArgumentException
     */
    public function get(int $index)
    {
        $this->update();
        if (isset($this->data[$index])) {
            return $this->updateValueIfNeeded($this->data, $index);
        }
        return null;
    }

    /**
     * Returns the first object or null if not found.
     * 
     * @return \IvoPetkov\DataObject|null The first object or null if not found.
     * @throws \InvalidArgumentException
     */
    public function getFirst()
    {
        $this->update();
        if (isset($this->data[0])) {
            return $this->updateValueIfNeeded($this->data, 0);
        }
        return null;
    }

    /**
     * Returns the last object or null if not found.
     * 
     * @return \IvoPetkov\DataObject|null The last object or null if not found.
     * @throws \InvalidArgumentException
     */
    public function getLast()
    {
        $this->update();
        $count = sizeof($this->data);
        if (isset($this->data[$count - 1])) {
            return $this->updateValueIfNeeded($this->data, $count - 1);
        }
        return null;
    }

    /**
     * Returns a random object from the list or null if the list is empty.
     * 
     * @return \IvoPetkov\DataObject|null A random object from the list or null if the list is empty.
     * @throws \InvalidArgumentException
     */
    public function getRandom()
    {
        $this->update();
        $count = sizeof($this->data);
        if ($count > 0) {
            $index = rand(0, $count - 1);
            if (isset($this->data[$index])) {
                return $this->updateValueIfNeeded($this->data, $index);
            }
        }
        return null;
    }

    /**
     * 
     */
    public function rewind(): void
    {
        $this->pointer = 0;
    }

    /**
     * 
     * @return \IvoPetkov\DataObject|null
     * @throws \InvalidArgumentException
     */
    public function current()
    {
        $this->update();
        if (isset($this->data[$this->pointer])) {
            return $this->updateValueIfNeeded($this->data, $this->pointer);
        }
        return null;
    }

    /**
     * 
     * @return int
     */
    public function key(): int
    {
        return $this->pointer;
    }

    /**
     * 
     */
    public function next(): void
    {
        ++$this->pointer;
    }

    /**
     * 
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function valid(): bool
    {
        $this->update();
        return isset($this->data[$this->pointer]);
    }

    /**
     * Applies the pending actions to the data list.
     * 
     * @throws \InvalidArgumentException
     */
    private function update()
    {
        $this->data = $this->updateData($this->data, $this->actions);
        $this->actions = [];
    }

    /**
     * Applies the actions to the data list provided.
     * 
     * @param mixed $data
     * @param array $actions
     * @throws \InvalidArgumentException
     * @return array Returns the updated data
     */
    private function updateData($data, $actions): array
    {
        if (is_callable($data)) {
            $context = new DataListContext();
            $contextActionsIndexes = [];
            foreach ($actions as $index => $action) {
                if ($action[0] === 'filterBy') {
                    $contextActionsIndexes[$index] = 'filterBy' . sizeof($context->filterByProperties);
                    $context->filterByProperties[] = new DataObject([
                        'property' => $action[1],
                        'value' => $action[2],
                        'operator' => $action[3],
                        'applied' => false
                    ]);
                } elseif ($action[0] === 'sortBy') {
                    $contextActionsIndexes[$index] = 'sortBy' . sizeof($context->sortByProperties);
                    $context->sortByProperties[] = new DataObject([
                        'property' => $action[1],
                        'order' => $action[2],
                        'applied' => false
                    ]);
                } elseif ($action[0] === 'sliceProperties') {
                    if (is_array($action[1])) {
                        foreach ($action[1] as $property) {
                            $context->requestedProperties[] = $property;
                        }
                    }
                }
            }
            $dataSource = call_user_func($data, $context);
            $hasRemovedActions = false;
            foreach ($context->filterByProperties as $index => $object) {
                if ($object->applied) {
                    $actionIndex = array_search('filterBy' . $index, $contextActionsIndexes);
                    if ($actionIndex !== false) {
                        unset($actions[$actionIndex]);
                        $hasRemovedActions = true;
                    }
                }
            }
            foreach ($context->sortByProperties as $index => $object) {
                if ($object->applied) {
                    $actionIndex = array_search('sortBy' . $index, $contextActionsIndexes);
                    if ($actionIndex !== false) {
                        unset($actions[$actionIndex]);
                        $hasRemovedActions = true;
                    }
                }
            }
            if ($hasRemovedActions) {
                $actions = array_values($actions);
            }
            if (is_array($dataSource) || $dataSource instanceof \Traversable) {
                $data = [];
                foreach ($dataSource as $value) {
                    $data[] = $value;
                }
            } else {
                throw new \InvalidArgumentException('The data source callback result is not iterable!');
            }
        }
        if (isset($actions[0])) {
            foreach ($actions as $action) {
                if ($action[0] === 'filter') {
                    $this->updateAllValuesIfNeeded($data);
                    $temp = [];
                    foreach ($data as $index => $object) {
                        if (call_user_func($action[1], $object) === true) {
                            $temp[] = $object;
                        }
                    }
                    $data = $temp;
                    unset($temp);
                } else if ($action[0] === 'filterBy') {
                    $this->updateAllValuesIfNeeded($data);
                    $temp = [];
                    foreach ($data as $object) {
                        $propertyName = $action[1];
                        $targetValue = $action[2];
                        $operator = $action[3];
                        $add = false;
                        if (!isset($object->$propertyName)) {
                            if ($operator === 'equal' && $targetValue === null) {
                                $add = true;
                            } elseif ($operator === 'notEqual' && $targetValue !== null) {
                                $add = true;
                            } elseif ($operator === 'inArray' && is_array($targetValue) && array_search(null, $targetValue) !== false) {
                                $add = true;
                            } elseif ($operator === 'notInArray' && !(is_array($targetValue) && array_search(null, $targetValue) !== false)) {
                                $add = true;
                            } else {
                                continue;
                            }
                        }
                        if (!$add) {
                            $value = $object->$propertyName;
                            if ($operator === 'equal') {
                                $add = $value === $targetValue;
                            } elseif ($operator === 'notEqual') {
                                $add = $value !== $targetValue;
                            } elseif ($operator === 'regExp') {
                                $add = preg_match('/' . $targetValue . '/', $value) === 1;
                            } elseif ($operator === 'notRegExp') {
                                $add = preg_match('/' . $targetValue . '/', $value) === 0;
                            } elseif ($operator === 'startWith') {
                                $add = substr($value, 0, strlen($targetValue)) === $targetValue;
                            } elseif ($operator === 'notStartWith') {
                                $add = substr($value, 0, strlen($targetValue)) !== $targetValue;
                            } elseif ($operator === 'endWith') {
                                $add = substr($value, -strlen($targetValue)) === $targetValue;
                            } elseif ($operator === 'notEndWith') {
                                $add = substr($value, -strlen($targetValue)) !== $targetValue;
                            } elseif ($operator === 'inArray') {
                                $add = is_array($targetValue) && array_search($value, $targetValue) !== false;
                            } elseif ($operator === 'notInArray') {
                                $add = !(is_array($targetValue) && array_search($value, $targetValue) !== false);
                            }
                        }
                        if ($add) {
                            $temp[] = $object;
                        }
                    }
                    $data = $temp;
                    unset($temp);
                } elseif ($action[0] === 'sort') {
                    $this->updateAllValuesIfNeeded($data);
                    usort($data, $action[1]);
                } elseif ($action[0] === 'sortBy') {
                    $this->updateAllValuesIfNeeded($data);
                    $sortData = []; // save the index and the property needed for the sort in a temp array
                    foreach ($data as $index => $object) {
                        $sortValue = isset($object->{$action[1]}) ? $object->{$action[1]} : null;
                        if (is_object($sortValue)) {
                            if ($sortValue instanceof \DateTime) {
                                $sortValue = $sortValue->getTimestamp();
                            } else {
                                $sortValue = null;
                            }
                        }
                        $sortData[] = [$index, $sortValue];
                    }
                    usort($sortData, function($value1SortData, $value2SortData) use ($action) {
                        if ($value1SortData[1] === null && $value2SortData[1] === null) {
                            $result = 0;
                        } else {
                            if ($value1SortData[1] === null) {
                                return $action[2] === 'asc' ? -1 : 1;
                            }
                            if ($value2SortData[1] === null) {
                                return $action[2] === 'asc' ? 1 : -1;
                            }
                            if ((is_int($value1SortData[1]) || is_float($value1SortData[1])) && (is_int($value2SortData[1]) || is_float($value2SortData[1]))) {
                                $result = $value1SortData[1] < $value2SortData[1] ? -1 : 1;
                            } else {
                                $result = strcmp($value1SortData[1], $value2SortData[1]);
                            }
                        }
                        if ($result === 0) { // if the sort property is the same, maintain the order by index
                            return $value1SortData[0] - $value2SortData[0];
                        }
                        return $result * ($action[2] === 'asc' ? 1 : -1);
                    });
                    $temp = [];
                    foreach ($sortData as $sortedValueData) {
                        $temp[] = $data[$sortedValueData[0]];
                    }
                    unset($sortData);
                    $data = $temp;
                    unset($temp);
                } elseif ($action[0] === 'reverse') {
                    $data = array_reverse($data);
                } elseif ($action[0] === 'shuffle') {
                    shuffle($data);
                } elseif ($action[0] === 'map') {
                    $this->updateAllValuesIfNeeded($data);
                    $data = array_map($action[1], $data);
                }
            }
        }
        return $data;
    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
    public function __set(string $name, $value): void
    {
        if ($name === 'length') {
            throw new \Exception('The property ' . get_class($this) . '::$' . $name . ' is readonly');
        }
        throw new \Exception('Undefined property: ' . get_class($this) . '::$' . $name);
    }

    /**
     * 
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function __get(string $name)
    {
        if ($name === 'length') {
            $this->update();
            return sizeof($this->data);
        }
        throw new \Exception('Undefined property: ' . get_class($this) . '::$' . $name);
    }

    /**
     * 
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name): bool
    {
        if ($name === 'length') {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param string $name
     * @throws \Exception
     */
    public function __unset(string $name): void
    {
        if ($name === 'length') {
            throw new \Exception('The property ' . get_class($this) . '::$' . $name . ' is readonly');
        }
        throw new \Exception('Undefined property: ' . get_class($this) . '::$' . $name);
    }

    /**
     * 
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /**
     * Filters the elements of the list using a callback function.
     * 
     * @param callable $callback The callback function to use.
     * @return \IvoPetkov\DataList A reference to the list.
     */
    public function filter(callable $callback): \IvoPetkov\DataList
    {
        $this->actions[] = ['filter', $callback];
        return $this;
    }

    /**
     * Filters the elements of the list by specific property value.
     * 
     * @param string $property The property name.
     * @param mixed $value The value of the property.
     * @param string $operator Available values: equal, notEqual, regExp, notRegExp, startWith, notStartWith, endWith, notEndWith, inArray, notInArray.
     * @return \IvoPetkov\DataList A reference to the list.
     * @throws \Exception
     */
    public function filterBy(string $property, $value, string $operator = 'equal'): \IvoPetkov\DataList
    {
        if (array_search($operator, ['equal', 'notEqual', 'regExp', 'notRegExp', 'startWith', 'notStartWith', 'endWith', 'notEndWith', 'inArray', 'notInArray']) === false) {
            throw new \Exception('Invalid operator (' . $operator . ')');
        }
        $this->actions[] = ['filterBy', $property, $value, $operator];
        return $this;
    }

    /**
     * Sorts the elements of the list using a callback function.
     * 
     * @param callable $callback The callback function to use.
     * @return \IvoPetkov\DataList A reference to the list.
     */
    public function sort(callable $callback): \IvoPetkov\DataList
    {
        $this->actions[] = ['sort', $callback];
        return $this;
    }

    /**
     * Sorts the elements of the list by specific property.
     * 
     * @param string $property The property name.
     * @param string $order The sort order.
     * @return \IvoPetkov\DataList A reference to the list.
     * @throws \Exception
     */
    public function sortBy(string $property, string $order = 'asc'): \IvoPetkov\DataList
    {
        if ($order !== 'asc' && $order !== 'desc') {
            throw new \Exception('The order argument \'asc\' or \'desc\'');
        }
        $this->actions[] = ['sortBy', $property, $order];
        return $this;
    }

    /**
     * Reverses the order of the objects in the list.
     * 
     * @return \IvoPetkov\DataList A reference to the list.
     */
    public function reverse(): \IvoPetkov\DataList
    {
        $this->actions[] = ['reverse'];
        return $this;
    }

    /**
     * Randomly reorders the objects in the list.
     * 
     * @return \IvoPetkov\DataList A reference to the list.
     */
    public function shuffle(): \IvoPetkov\DataList
    {
        $this->actions[] = ['shuffle'];
        return $this;
    }

    /**
     * Applies the callback to the objects of the list.
     * 
     * @param callable $callback The callback function to use.
     * @return \IvoPetkov\DataList A reference to the list.
     */
    public function map(callable $callback): \IvoPetkov\DataList
    {
        $this->actions[] = ['map', $callback];
        return $this;
    }

    /**
     * Prepends an object to the beginning of the list.
     * 
     * @param \IvoPetkov\DataObject|array $object The data to be prepended.
     * @return \IvoPetkov\DataList A reference to the list.
     * @throws \InvalidArgumentException
     */
    public function unshift($object): \IvoPetkov\DataList
    {
        $this->update();
        array_unshift($this->data, $object);
        return $this;
    }

    /**
     * Shift an object off the beginning of the list.
     * 
     * @return \IvoPetkov\DataObject|null Returns the shifted object or null if the list is empty.
     * @throws \InvalidArgumentException
     */
    public function shift()
    {
        $this->update();
        if (isset($this->data[0])) {
            $this->updateValueIfNeeded($this->data, 0);
            return array_shift($this->data);
        }
        return null;
    }

    /**
     * Pushes an object onto the end of the list.
     * 
     * @param \IvoPetkov\DataObject|array $object The data to be pushed.
     * @return \IvoPetkov\DataList A reference to the list.
     * @throws \InvalidArgumentException
     */
    public function push($object): \IvoPetkov\DataList
    {
        $this->update();
        array_push($this->data, $object);
        return $this;
    }

    /**
     * Pops an object off the end of the list.
     * 
     * @return \IvoPetkov\DataObject|null Returns the popped object or null if the list is empty.
     * @throws \InvalidArgumentException
     */
    public function pop()
    {
        $this->update();
        if (isset($this->data[0])) {
            $this->updateValueIfNeeded($this->data, sizeof($this->data) - 1);
            return array_pop($this->data);
        }
        return null;
    }

    /**
     * Appends the items of the list provided to the current list.
     * 
     * @param \IvoPetkov\DataList $list A list to append after the current one.
     * @return \IvoPetkov\DataList A reference to the list.
     * @throws \InvalidArgumentException
     */
    public function concat(\IvoPetkov\DataList $list): \IvoPetkov\DataList
    {
        $this->update();
        foreach ($list as $object) {
            array_push($this->data, $object);
        }
        return $this;
    }

    /**
     * Extract a slice of the list.
     * 
     * @param int $offset The index position where the extraction should begin
     * @param int $length The max length of the items in the extracted slice.
     * @return \IvoPetkov\DataList Returns a slice of the list.
     * @throws \InvalidArgumentException
     */
    public function slice(int $offset, int $length = null): \IvoPetkov\DataList
    {
        $this->update();
        $slice = array_slice($this->data, $offset, $length);
        $className = get_class($this);
        return new $className($slice);
    }

    /**
     * Returns a new list of object that contain only the specified properties of the objects in the current list.
     * 
     * @param array $properties The list of property names.
     * @return \IvoPetkov\DataList Returns a new list.
     */
    public function sliceProperties(array $properties)
    {
        $actions = $this->actions;
        $actions[] = ['sliceProperties', $properties];
        $data = $this->updateData($this->data, $actions);
        $list = new \IvoPetkov\DataList();
        $tempObject = new \IvoPetkov\DataObject();
        foreach ($data as $index => $object) {
            $object = $this->updateValueIfNeeded($data, $index);
            $newObject = clone($tempObject);
            foreach ($properties as $property) {
                $newObject->$property = isset($object->$property) ? $object->$property : null;
            }
            $list[] = $newObject;
        }
        return $list;
    }

    /**
     * Returns the list data converted as an array.
     * 
     * @return array The list data converted as an array.
     * @throws \InvalidArgumentException
     */
    public function toArray(): array
    {
        $this->update();

        // Copied from DataObjectToArrayTrait. Do not modify here !!!
        $toArray = function($object) use (&$toArray) {
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
            if (isset($object->internalDataObjectData)) {
                foreach ($object->internalDataObjectData as $name => $value) {
                    $result[substr($name, 1)] = null;
                }
            }
            ksort($result);
            foreach ($result as $name => $null) {
                $value = $object instanceof \ArrayAccess ? $object[$name] : (isset($object->$name) ? $object->$name : null);
                if (is_object($value)) {
                    if (method_exists($value, 'toArray')) {
                        $result[$name] = $value->toArray();
                    } else {
                        if ($value instanceof \DateTime) {
                            $result[$name] = $value->format('c');
                        } else {
                            $propertyVars = $toArray($value);
                            foreach ($propertyVars as $propertyVarName => $propertyVarValue) {
                                if (is_object($propertyVarValue)) {
                                    $propertyVars[$propertyVarName] = $toArray($propertyVarValue);
                                }
                            }
                            $result[$name] = $propertyVars;
                        }
                    }
                } else {
                    $result[$name] = $value;
                }
            }
            return $result;
        };

        $result = [];
        foreach ($this->data as $index => $object) {
            $object = $this->updateValueIfNeeded($this->data, $index);
            if (method_exists($object, 'toArray')) {
                $result[] = $object->toArray();
            } else {
                $result[] = $toArray($object);
            }
        }
        return $result;
    }

    /**
     * Returns the list data converted as JSON.
     * 
     * @return string The list data converted as JSON.
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function toJSON(): string
    {
        $this->update();

        // Copied from DataObjectToJSONTrait. Do not modify here !!!
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

        $json = '';
        foreach ($this->data as $index => $object) {
            $object = $this->updateValueIfNeeded($this->data, $index);
            if (method_exists($object, 'toJSON')) {
                $json .= $object->toJSON() . ',';
            } else {
                $json .= $toJSON($object) . ',';
            }
        }
        $json = '[' . rtrim($json, ',') . ']';
        return $json;
    }

}
