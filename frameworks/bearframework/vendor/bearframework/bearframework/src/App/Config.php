<?php

/*
 * Bear Framework
 * http://bearframework.com
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework\App;

/**
 * The application configuration.
 * 
 * @property string|null $appDir The directory where the application source files are located.
 * @property string|null $dataDir The directory where the application data are located.
 * @property string|null $logsDir The directory where the application log files are located.
 * @property bool $updateEnvironment Update the PHP environment to make it work better.
 * @property bool $handleErrors Automatically handle errors and display friendly responses.
 * @property bool $displayErrors Display the first handled error. Useful while developing.
 * @property bool $logErrors Log the handled errors.
 * @property string $assetsPathPrefix The prefix of the assets URLs.
 */
class Config
{

    use \IvoPetkov\DataObjectTrait;
    use \IvoPetkov\DataObjectToArrayTrait;
    use \IvoPetkov\DataObjectToJSONTrait;

    /**
     * 
     * @param array $options An array of configuration options.
     * @throws \Exception
     */
    public function __construct(array $options = [])
    {
        $this->defineProperty('appDir', [
            'type' => '?string',
            'set' => function($value) {
                if ($value === null) {
                    return null;
                }
                $value = realpath($value);
                if ($value === false) {
                    throw new \Exception('The value of the appDir option is not a real directory');
                }
                return $value;
            }
        ]);
        $this->defineProperty('dataDir', [
            'type' => '?string',
            'set' => function($value) {
                if ($value === null) {
                    return null;
                }
                $value = realpath($value);
                if ($value === false) {
                    throw new \Exception('The value of the dataDir option is not a real directory');
                }
                return $value;
            }
        ]);
        $this->defineProperty('logsDir', [
            'type' => '?string',
            'set' => function($value) {
                if ($value === null) {
                    return null;
                }
                $value = realpath($value);
                if ($value === false) {
                    throw new \Exception('The value of the logsDir option is not a real directory');
                }
                return $value;
            }
        ]);
        $this->defineProperty('updateEnvironment', [
            'type' => 'bool',
            'init' => function() {
                return true;
            }
        ]);
        $this->defineProperty('handleErrors', [
            'type' => 'bool',
            'init' => function() {
                return true;
            }
        ]);
        $this->defineProperty('displayErrors', [
            'type' => 'bool',
            'init' => function() {
                return false;
            }
        ]);
        $this->defineProperty('logErrors', [
            'type' => 'bool',
            'init' => function() {
                return false;
            }
        ]);
        $this->defineProperty('assetsPathPrefix', [
            'type' => 'string',
            'set' => function($value) {
                if (!isset($value{0})) {
                    throw new \Exception('The value of the assetsPathPrefix option cannot be empty.');
                }
                return $value;
            },
            'init' => function() {
                return '/assets/';
            }
        ]);

        foreach ($options as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Loads a configuration file. The file must return PHP array containing configuration options in the format ['option1'=>'value1', 'option2'=>'value2'].
     * 
     * @param string $filename The filename containing the configuration options.
     * @throws \InvalidArgumentException
     */
    public function load(string $filename): \BearFramework\App\Config
    {
        $filename = realpath($filename);
        if ($filename === false) {
            throw new \InvalidArgumentException('The filename specified (' . $filename . ') is not valid');
        }
        ob_start();
        try {
            $data = (static function($__filename) {
                        return include $__filename;
                    })($filename);
            ob_end_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
        if (is_array($data)) {
            foreach ($data as $name => $value) {
                $this->$name = $value;
            }
            return $this;
        }
        throw new \InvalidArgumentException('The configuration data in ' . $filename . ' is not valid');
    }

}
