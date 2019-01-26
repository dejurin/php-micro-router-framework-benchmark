<?php

/*
 * Docs Generator
 * https://github.com/ivopetkov/docs-generator
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

use IvoPetkov\DocsGenerator\ClassParser;

/**
 * 
 */
class DocsGenerator
{

    /**
     *
     * @var string 
     */
    private $projectDir = null;

    /**
     *
     * @var array 
     */
    private $sourceDirs = [];

    /**
     * 
     * @param string $projectDir
     * @param array $sourceDirs
     * @throws \InvalidArgumentException
     */
    public function __construct(string $projectDir, array $sourceDirs)
    {
        if (!is_dir($projectDir)) {
            throw new \InvalidArgumentException('The projectDir specified (' . $projectDir . ') is not a valid dir!');
        }
        $this->projectDir = str_replace('\\', '/', realpath($projectDir));
        foreach ($sourceDirs as $sourceDir) {
            $sourceDir = '/' . trim($sourceDir, '/\\');
            if (!is_dir($this->projectDir . $sourceDir)) {
                throw new \InvalidArgumentException('The sourceDir specified (' . $this->projectDir . $sourceDir . ') is not a valid dir!');
            }
            $this->sourceDirs[] = $sourceDir;
        }
    }

    public function generateMarkdown(string $outputDir)
    {
        $this->generate($outputDir, 'markdown');
    }

    private function isInSourcesDirs(string $filename)
    {
        foreach ($this->sourceDirs as $sourceDir) {
            if (strpos(str_replace('\\', '/', $filename), $this->projectDir . $sourceDir . '/') === 0) {
                return true;
                break;
            }
        }
        return false;
    }

    private function generate(string $outputDir, string $type)
    {
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
        $outputDir = rtrim($outputDir, '\/');
        $classNames = [];
        foreach ($this->sourceDirs as $i => $sourceDir) {
            $files = $this->getFiles($this->projectDir . $sourceDir);
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if (preg_match('/class [a-zA-Z]*/', $content)) {
                    $declaredClasses = get_declared_classes();
                    require_once $file;
                    $newClasses = array_values(array_diff(get_declared_classes(), $declaredClasses));
                    foreach ($newClasses as $newClassName) {
                        if (strpos($newClassName, 'class@anonymous') === 0) {
                            continue;
                        }
                        $classNames[$newClassName] = str_replace($this->projectDir, '', str_replace('\\', '/', $file));
                    }
                }
            }
        }

        ksort($classNames);

        $writeFile = function(string $filename, string $content) use ($outputDir) {
            $filename = $outputDir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($filename, $content);
        };

        $temp = [];
        foreach ($classNames as $className => $classSourceFile) {
            $classData = ClassParser::parse($className);
            if ($classData['internal']) {
                continue;
            }
            if (!$this->isInSourcesDirs($classData['filename'])) {
                continue;
            }
            $temp[$className] = $classSourceFile;
        }
        $classNames = $temp;

        $indexOutput = '';
        $indexOutput .= '## Classes' . "\n\n";
        foreach ($classNames as $className => $classSourceFile) {
            $classData = ClassParser::parse($className);

            $classOutput = '';

            $classOutput .= '# ' . $className . "\n\n";

            if (!empty($classData['extends'])) {
                $classOutput .= "extends " . $this->getType((string) $classData['extends']) . "\n\n";
            }

            if (!empty($classData['implements'])) {
                $implements = array_map(function($value) {
                    return $this->getType((string) $value);
                }, $classData['implements']);
                $classOutput .= "implements " . implode(', ', $implements) . "\n\n";
            }

            if (!empty($classData['description'])) {
                $classOutput .= $classData['description'] . "\n\n";
            }

            if (!empty($classData['constants'])) {
                usort($classData['constants'], function($data1, $data2) {
                    return strcmp($data1['name'], $data2['name']);
                });
                $constantsOutput = '';
                foreach ($classData['constants'] as $constantData) {
                    if ($constantData['class'] !== $className) {
                        $constantsOutput .= "##### const " . $this->getType((string) $constantData['type']) . ' ' . $constantData['name'] . "\n\n";
                        if (!empty($constantData['description'])) {
                            $constantsOutput .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $constantData['description'] . "\n\n";
                        }
                    }
                }
                if (!empty($constantsOutput)) {
                    $classOutput .= '## Constants' . "\n\n";
                    $classOutput .= $constantsOutput;
                }
            }

            if (!empty($classData['properties'])) {
                usort($classData['properties'], function($data1, $data2) {
                    return strcmp($data1['name'], $data2['name']);
                });
                $propertiesOutput = '';
                $inheritedProperties = [];
                foreach ($classData['properties'] as $propertyData) {
                    if ($propertyData['isPrivate']) {
                        continue;
                    }
                    $keywords = [];
                    if ($propertyData['isPublic']) {
                        $keywords[0] = 'public';
                    }
                    if ($propertyData['isProtected']) {
                        $keywords[1] = 'protected';
                    }
                    if ($propertyData['isPrivate']) {
                        $keywords[2] = 'private';
                    }
                    if ($propertyData['isStatic']) {
                        $keywords[3] = 'static';
                    }
                    if ($propertyData['isReadOnly']) {
                        $keywords[4] = 'readonly';
                    }
                    ksort($keywords);
                    $propertyOutput = "##### " . implode(' ', $keywords) . ' ' . $this->getType((string) $propertyData['type']) . ' $' . $propertyData['name'] . "\n\n";
                    if ($propertyData['class'] !== $className) {
                        if (!isset($inheritedProperties[$propertyData['class']])) {
                            $inheritedProperties[$propertyData['class']] = [];
                        }
                        $inheritedProperties[$propertyData['class']][] = $propertyOutput;
                        continue;
                    }
                    $propertiesOutput .= $propertyOutput;
                    if (!empty($propertyData['description'])) {
                        $propertiesOutput .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $propertyData['description'] . "\n\n";
                    }
                }
                ksort($inheritedProperties);
                foreach ($inheritedProperties as $inheritedClassName => $inheritedPropertiesOutput) {
                    $propertiesOutput .= '### Inherited from ' . $this->getType($inheritedClassName) . ":\n\n";
                    $propertiesOutput .= implode('', $inheritedPropertiesOutput);
                }
                if (!empty($propertiesOutput)) {
                    $classOutput .= '## Properties' . "\n\n";
                    $classOutput .= $propertiesOutput;
                }
            }

            if (!empty($classData['methods'])) {
                usort($classData['methods'], function($data1, $data2) {
                    if ((int) $data1['isPublic'] . (int) $data1['isProtected'] . (int) $data1['isPrivate'] !== (int) $data2['isPublic'] . (int) $data2['isProtected'] . (int) $data2['isPrivate']) {
                        if ($data1['isPublic']) {
                            return -1;
                        }
                        if ($data1['isPrivate']) {
                            return 1;
                        }
                        return 1;
                    }
                    return strcmp($data1['name'], $data2['name']);
                });
                $methodsOutput = '';
                $inheritedMethods = [];
                foreach ($classData['methods'] as $methodData) {
                    if ($methodData['isPrivate'] || (substr($methodData['name'], 0, 2) === '__' && $methodData['name'] !== '__construct')) {
                        continue;
                    }
                    if ($methodData['class'] !== $className) {
                        if (!isset($inheritedMethods[$methodData['class']])) {
                            $inheritedMethods[$methodData['class']] = [];
                        }
                        $richOutput = true;
                        $methodClassData = ClassParser::parse($methodData['class']);
                        if (!$this->isInSourcesDirs($methodClassData['filename'])) {
                            $richOutput = false;
                        }
                        $inheritedMethods[$methodData['class']][] = "##### " . $this->getMethod($methodData) . "\n\n";
                        continue;
                    }
                    $methodsOutput .= "##### " . $this->getMethod($methodData) . "\n\n";
                    if (!empty($methodData['description'])) {
                        $methodsOutput .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $methodData['description'] . "\n\n";
                    }
                    $returnDescription = is_array($methodData['return']) ? $methodData['return']['description'] : '';
                    if (!empty($returnDescription) && $methodData['return']['type'] !== 'void') {
                        $methodsOutput .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns: ' . $returnDescription . "\n\n";
                    }

                    $methodOutput = '';
                    $methodOutput .= '# ' . $methodData['class'] . '::' . $methodData['name'] . "\n\n";
                    if (!empty($methodData['description'])) {
                        $methodOutput .= $methodData['description'] . "\n\n";
                    }
                    $methodOutput .= "```php\n" . $this->getMethod($methodData, false) . "\n```\n\n";
                    if (!empty($methodData['parameters'])) {
                        $methodOutput .= '## Parameters' . "\n\n";
                        foreach ($methodData['parameters'] as $i => $parameter) {
                            $methodOutput .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$" . $parameter['name'] . "`\n\n";
                            if (!empty($parameter['description'])) {
                                $methodOutput .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $parameter['description'] . "\n\n";
                            }
                        }
                    }

                    if ($methodData['name'] !== '__construct') {
                        $returnDescription = is_array($methodData['return']) ? $methodData['return']['description'] : '';
                        if (!empty($returnDescription)) {
                            $methodOutput .= '## Returns' . "\n\n";
                            $methodOutput .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $returnDescription . "\n\n";
                        }
                    }

                    $methodOutput .= '## Details' . "\n\n";
                    $methodOutput .= "Class: [" . $className . "](" . $this->getClassOutputFilename($className) . ")\n\n";
                    $methodOutput .= "File: " . str_replace('\\', '/', $classSourceFile) . "\n\n";
                    $methodOutput .= '---' . "\n\n" . '[back to index](index.md)' . "\n\n";

                    $writeFile($this->getMethodOutputFilename($className, $methodData['name']), $methodOutput);
                }
                ksort($inheritedMethods);
                foreach ($inheritedMethods as $inheritedClassName => $inheritedMethodsOutput) {
                    $methodsOutput .= '### Inherited from ' . $this->getType($inheritedClassName) . ":\n\n";
                    $methodsOutput .= implode('', $inheritedMethodsOutput);
                }
                if (!empty($methodsOutput)) {
                    $classOutput .= '## Methods' . "\n\n";
                    $classOutput .= $methodsOutput;
                }
            }

            if (!empty($classData['events'])) {
                usort($classData['events'], function($data1, $data2) {
                    return strcmp($data1['name'], $data2['name']);
                });
                $eventsOutput = '';
                foreach ($classData['events'] as $eventData) {
                    $eventsOutput .= "##### " . $eventData['name'] . "\n\n";
                    $eventsOutput .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Type: " . $this->getType($eventData['type']) . "\n\n";
                    if (!empty($eventData['description'])) {
                        $eventsOutput .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $eventData['description'] . "\n\n";
                    }
                }
                if (!empty($eventsOutput)) {
                    $classOutput .= '## Events' . "\n\n";
                    $classOutput .= $eventsOutput;
                }
            }

            $classOutput .= '## Details' . "\n\n";
            $classOutput .= "File: " . str_replace('\\', '/', $classSourceFile) . "\n\n";
            $classOutput .= '---' . "\n\n" . '[back to index](index.md)' . "\n\n";

            $writeFile($this->getClassOutputFilename($className), $classOutput);

            $indexOutput .= '### [' . $className . '](' . $this->getClassOutputFilename($className) . ')' . "\n\n";
            if (!empty($classData['description'])) {
                $indexOutput .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $classData['description'] . "\n\n";
            }
        }

        $writeFile('index.md', $indexOutput);
    }

    /**
     * 
     * @param string $class
     * @param string $method
     * @return string
     */
    private function getMethodOutputFilename(string $class, string $method): string
    {
        return str_replace('\\', '.', strtolower($class . '.' . $method)) . '.method.md';
    }

    /**
     * 
     * @param string $class
     * @return string
     */
    private function getClassOutputFilename(string $class): string
    {
        return str_replace('\\', '.', strtolower($class)) . '.class.md';
    }

    /**
     * 
     * @param mixed $value
     * @return string
     */
    private function getValue($value): string
    {
        if (is_string($value)) {
            return '\'' . str_replace('\'', '\\\'', $value) . '\'';
        }
        return json_encode($value);
    }

    /**
     * 
     * @param string $type
     * @param bool $richOutput
     * @return string
     */
    private function getType(string $type, bool $richOutput = true): string
    {
        $parts = explode('|', $type);
        foreach ($parts as $i => $part) {
            $part = trim(trim($part), '\\');
            if ($richOutput) {
                if ($part !== 'void' && $part !== 'string' && $part !== 'int' && $part !== 'boolean' && $part !== 'array') {
                    $class = $part;
                    if (substr($class, -2) === '[]') {
                        $class = substr($class, 0, -2);
                    }
                    $classData = ClassParser::parse($class);
                    if (is_array($classData)) {
                        if (strlen($classData['extension']) > 0) {
                            $part = '[' . $part . '](http://php.net/manual/en/class.' . strtolower($class) . '.php)';
                        } else {
                            if ($classData['internal']) {
                                continue;
                            }
                            if (!$this->isInSourcesDirs($classData['filename'])) {
                                continue;
                            }
                            $part = '[' . $part . '](' . $this->getClassOutputFilename($class) . ')';
                        }
                    }
                }
            }
            $parts[$i] = $part;
        }
        return implode('|', $parts);
    }

    /**
     * 
     * @param array $method
     * @param bool $richOutput
     * @return string
     */
    private function getMethod(array $method, bool $richOutput = true): string
    {
        $result = '';
        $keywords = [];
        if ($method['isPublic']) {
            $keywords[0] = 'public';
        }
        if ($method['isProtected']) {
            $keywords[1] = 'protected';
        }
        if ($method['isPrivate']) {
            $keywords[2] = 'private';
        }
        if ($method['isStatic']) {
            $keywords[3] = 'static';
        }
        if ($method['isAbstract']) {
            $keywords[4] = 'abstract';
        }
        if ($method['isFinal']) {
            $keywords[5] = 'final';
        }
        ksort($keywords);

        $classData = ClassParser::parse($method['class']);

        if (empty($method['parameters'])) {
            $parameters = 'void';
        } else {
            $parameters = '';
            $bracketsToAddInTheEnd = 0;
            foreach ($method['parameters'] as $parameter) {
                if ($parameter['isOptional']) {
                    $parameters .= ' [, ';
                    $bracketsToAddInTheEnd++;
                } else {
                    $parameters .= ' , ';
                }
                $parameters .= $this->getType((string) $parameter['type'], $richOutput) . ' $' . $parameter['name'] . ($parameter['value'] !== null ? ' = ' . $this->getValue($parameter['value']) : '');
            }
            if ($bracketsToAddInTheEnd > 0) {
                $parameters .= ' ' . str_repeat(']', $bracketsToAddInTheEnd) . ' ';
            }
            $parameters = trim($parameters, ' ,');
            if (substr($parameters, 0, 2) === '[,') {
                $parameters = '[' . substr($parameters, 2);
            }
        }
        $returnType = isset($method['return']['type']) ? (string) $method['return']['type'] : 'void';
        $name = $method['name'];
        $url = strlen($classData['extension']) > 0 ? 'http://php.net/manual/en/' . strtolower($method['class'] . '.' . $name) . '.php' : $this->getMethodOutputFilename($method['class'], $name);
        $result .= implode(' ', $keywords) . ($method['isConstructor'] || $method['isDestructor'] ? '' : ' ' . $this->getType($returnType, $richOutput)) . ' ' . ($richOutput ? '[' . $name . '](' . $url . ')' : $name) . ' ( ' . $parameters . ' )' . "\n";
        return trim($result);
    }

    /**
     * 
     * @param string $dir
     * @param string $extension
     * @return array
     */
    private function getFiles(string $dir, string $extension = 'php'): array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $dir = realpath($dir);
        $files = scandir($dir);
        $result = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.git' || substr($file, 0, 1) === '_') {
                continue;
            }
            if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                $result = array_merge($result, $this->getFiles($dir . DIRECTORY_SEPARATOR . $file));
            } else {
                if (pathinfo($file, PATHINFO_EXTENSION) === $extension) {
                    $result[] = $dir . DIRECTORY_SEPARATOR . $file;
                }
            }
        }
        return $result;
    }

}
