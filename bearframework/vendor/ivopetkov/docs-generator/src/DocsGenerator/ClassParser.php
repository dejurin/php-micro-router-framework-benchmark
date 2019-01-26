<?php

/*
 * Docs Generator
 * https://github.com/ivopetkov/docs-generator
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov\DocsGenerator;

/**
 * 
 */
class ClassParser
{

    static private $cache = [];

    /**
     * 
     * @param string $class
     * @return array|null
     */
    static function parse(string $class)
    {
        if (!isset(self::$cache[$class])) {
            $result = [];
            if (!class_exists($class) && !interface_exists($class)) {
                return null;
            }
            $reflectionClass = new \ReflectionClass($class);

            $result['name'] = $reflectionClass->name;
            $result['namespace'] = $reflectionClass->getNamespaceName();
            $result['filename'] = $reflectionClass->getFileName();
            $classComments = self::parseDocComment($reflectionClass->getDocComment());

            $parentClass = $reflectionClass->getParentClass();
            $result['extends'] = $parentClass instanceof \ReflectionClass ? $parentClass->name : null;
            $result['implements'] = $reflectionClass->getInterfaceNames();
            $result['internal'] = $classComments['internal'];

            $methodsToSkip = [];
            if (array_search('ArrayAccess', $result['implements']) !== false) {
                $methodsToSkip = array_merge($methodsToSkip, ['offsetGet', 'offsetExists', 'offsetSet', 'offsetUnset']);
            }
            if (array_search('Iterator', $result['implements']) !== false) {
                $methodsToSkip = array_merge($methodsToSkip, ['current', 'next', 'key', 'valid', 'rewind']);
            }

            $result['description'] = isset($classComments['description']) ? $classComments['description'] : '';

            $result['constants'] = [];
            $constants = $reflectionClass->getConstants();
            foreach ($constants as $name => $value) {
                $constant = $reflectionClass->getReflectionConstant($name);
                $constantComments = self::parseDocComment($constant->getDocComment());
                $result['constants'][] = [
                    'name' => $name,
                    'class' => $constant->class,
                    'value' => $value,
                    'type' => $value !== null ? self::updateType(gettype($value)) : null,
                    'description' => isset($constantComments['description']) ? $constantComments['description'] : '',
                ];
            }

            $result['properties'] = [];
            $properties = $reflectionClass->getProperties();
            $defaultProperties = $reflectionClass->getDefaultProperties();
            foreach ($properties as $property) {
                $value = isset($defaultProperties[$property->name]) ? $defaultProperties[$property->name] : null;
                $propertyComments = self::parseDocComment($property->getDocComment());
                $result['properties'][] = [
                    'name' => $property->name,
                    'class' => $property->class,
                    'value' => $value,
                    'type' => $propertyComments['type'] !== null ? $propertyComments['type'] : ($value !== null ? self::updateType(gettype($value)) : null),
                    'isPrivate' => $property->isPrivate(),
                    'isProtected' => $property->isProtected(),
                    'isPublic' => $property->isPublic(),
                    'isStatic' => $property->isStatic(),
                    'isReadOnly' => false,
                    'description' => isset($propertyComments['description']) ? $propertyComments['description'] : '',
                ];
            }

            if (!empty($classComments['properties'])) {
                foreach ($classComments['properties'] as $propertyComments) {
                    $result['properties'][] = [
                        'name' => $propertyComments['name'],
                        'class' => $class,
                        'value' => null,
                        'type' => $propertyComments['type'],
                        'isPrivate' => false,
                        'isProtected' => false,
                        'isPublic' => true,
                        'isStatic' => false,
                        'isReadOnly' => isset($propertyComments['readonly']) ? $propertyComments['readonly'] : false,
                        'description' => isset($propertyComments['description']) ? $propertyComments['description'] : '',
                    ];
                }
            }

            $result['methods'] = [];
            $methods = $reflectionClass->getMethods();
            foreach ($methods as $method) {
                if (array_search($method->name, $methodsToSkip) !== false) {
                    continue;
                }
                $parameters = $method->getParameters();
                $parametersData = [];
                $methodComments = self::parseDocComment($method->getDocComment());
                foreach ($parameters as $i => $parameter) {
                    $value = null;
                    $type = null;
                    if ($parameter->hasType()) {
                        $type = self::updateType((string) $parameter->getType());
                    }
                    if ($parameter->isOptional()) {
                        if ($parameter->isDefaultValueAvailable()) {
                            $value = $parameter->getDefaultValue();
                        }
                        if ($type === null && $value !== null) {
                            $type = self::updateType(gettype($value));
                        }
                    }
                    $description = '';
                    if (isset($methodComments['parameters'][$i]) && $methodComments['parameters'][$i]['name'] === $parameter->name) {
                        $type = $methodComments['parameters'][$i]['type'];
                        $description = $methodComments['parameters'][$i]['description'];
                    }
                    $parametersData[] = [
                        'name' => $parameter->name,
                        'value' => $value,
                        'type' => $type,
                        'isOptional' => $parameter->isOptional(),
                        'description' => $description,
                    ];
                }
                $result['methods'][] = [
                    'name' => $method->name,
                    'class' => $method->class,
                    'parameters' => $parametersData,
                    'isPrivate' => $method->isPrivate(),
                    'isProtected' => $method->isProtected(),
                    'isPublic' => $method->isPublic(),
                    'isStatic' => $method->isStatic(),
                    'isAbstract' => $method->isAbstract(),
                    'isFinal' => $method->isFinal(),
                    'isConstructor' => $method->isConstructor(),
                    'isDestructor' => $method->isDestructor(),
                    'description' => isset($methodComments['description']) ? $methodComments['description'] : '',
                    'return' => isset($methodComments['return']) ? $methodComments['return'] : '',
                ];
            }

            $result['extension'] = $reflectionClass->getExtensionName();

            $result['events'] = [];
            foreach ($classComments['events'] as $eventComments) {
                $result['events'][] = [
                    'name' => $eventComments['name'],
                    'type' => $eventComments['type'],
                    'description' => $eventComments['description']
                ];
            }

            self::$cache[$class] = $result;
        }
        return self::$cache[$class];
    }

    /**
     * 
     * @param string $comment
     * @return array
     */
    private static function parseDocComment(string $comment): array
    {
        $comment = trim($comment, "/* \n\r\t");
        $lines = explode("\n", $comment);
        $temp = [];
        foreach ($lines as $line) {
            $line = trim($line, " *");
            $line = trim($line);
            if (isset($line{0})) {
                $temp[] = $line;
            }
        }
        $lines = $temp;
        $result = [];
        $result['description'] = '';
        $result['type'] = null;
        $result['parameters'] = [];
        $result['return'] = null;
        $result['exceptions'] = [];
        $result['properties'] = [];
        $result['events'] = [];
        $result['internal'] = false;

        foreach ($lines as $i => $line) {
            if ($line[0] === '@') {
                break;
            }
            $result['description'] .= $line . "\n";
            unset($lines[$i]);
        }
        $result['description'] = trim($result['description']);

        $previousTypedLineIndex = null;
        foreach ($lines as $i => $line) {
            if ($line[0] !== '@') {
                if ($previousTypedLineIndex !== null) {
                    $lines[$previousTypedLineIndex] .= "\n" . $lines[$i];
                }
                unset($lines[$i]);
                continue;
            }
            $previousTypedLineIndex = $i;
        }

        foreach ($lines as $line) {
            if ($line[0] === '@') {
                $lineParts = explode(' ', $line, 2);
                $tag = trim($lineParts[0]);
                $value = isset($lineParts[1]) ? trim($lineParts[1]) : '';
                if ($tag === '@param') {
                    $valueParts = explode(' ', $value, 3);
                    $result['parameters'][] = [
                        'name' => isset($valueParts[1]) ? trim($valueParts[1], ' $') : null,
                        'type' => isset($valueParts[0]) ? self::updateType(trim($valueParts[0])) : null,
                        'description' => isset($valueParts[2]) ? trim($valueParts[2]) : null,
                    ];
                } elseif ($tag === '@return') {
                    $valueParts = explode(' ', $value, 2);
                    $result['return'] = [
                        'type' => isset($valueParts[0]) ? self::updateType(trim($valueParts[0])) : null,
                        'description' => isset($valueParts[1]) ? trim($valueParts[1]) : null,
                    ];
                } elseif ($tag === '@throws') {
                    $result['exceptions'][] = $value;
                } elseif ($tag === '@var') {
                    $result['type'] = self::updateType($value);
                } elseif ($tag === '@property' || $tag === '@property-read' || $tag === '@property-write') {
                    $valueParts = explode(' ', $value, 3);
                    $result['properties'][] = [
                        'name' => isset($valueParts[1]) ? trim($valueParts[1], ' $') : null,
                        'type' => isset($valueParts[0]) ? self::updateType(trim($valueParts[0])) : null,
                        'description' => isset($valueParts[2]) ? trim($valueParts[2]) : null,
                        'readonly' => $tag === '@property-read'
                    ];
                } elseif ($tag === '@internal') {
                    $result['internal'] = true;
                } elseif ($tag === '@event') {
                    $valueParts = explode(' ', $value, 3);
                    $result['events'][] = [
                        'name' => isset($valueParts[1]) ? trim($valueParts[1], ' ') : null,
                        'type' => isset($valueParts[0]) ? self::updateType(trim($valueParts[0])) : null,
                        'description' => isset($valueParts[2]) ? trim($valueParts[2]) : null
                    ];
                }
            }
        }
        $result['exceptions'] = array_unique($result['exceptions']);
        return $result;
    }

    /**
     * 
     * @param string $type
     * @return string
     */
    private static function updateType(string $type): string
    {
        $parts = explode('|', $type);
        $result = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === 'integer') {
                $part = 'int';
            } elseif ($part === 'boolean') {
                $part = 'bool';
            }
            if (isset($part[0])) {
                $result[] = $part;
            }
        }
        return implode('|', $result);
    }

}
