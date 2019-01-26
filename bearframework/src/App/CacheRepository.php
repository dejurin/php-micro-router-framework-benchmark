<?php

/*
 * Bear Framework
 * http://bearframework.com
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework\App;

use BearFramework\App\CacheItem;

/**
 * Data cache
 * @event \BearFramework\App\Cache\ItemRequestEvent itemRequest An event dispatched after a cache item is requested.
 * @event \BearFramework\App\Cache\ItemChangeEvent itemChange An event dispatched after a cache item is changed.
 * @event \BearFramework\App\Cache\ItemSetEvent itemSet An event dispatched after a cache item is added or updated.
 * @event \BearFramework\App\Cache\ItemGetEvent itemGet An event dispatched after a cache item is requested.
 * @event \BearFramework\App\Cache\ItemGetValueEvent itemGetValue An event dispatched after the value of a cache item is requested.
 * @event \BearFramework\App\Cache\ItemExistsEvent itemExists An event dispatched after a cache item is checked for existence.
 * @event \BearFramework\App\Cache\ItemDeleteEvent itemDelete An event dispatched after a cache item is deleted.
 * @event \BearFramework\App\Cache\ClearEvent clear An event dispatched after the cache is cleared.
 */
class CacheRepository
{

    use \BearFramework\App\EventsTrait;

    /**
     *
     */
    private $newCacheItemCache = null;

    /**
     *
     * @var ?\BearFramework\App\ICacheDriver  
     */
    private $driver = null;

    /**
     *
     * @var \BearFramework\App 
     */
    private $app = null;

    /**
     * 
     * @param \BearFramework\App $app
     */
    public function __construct(\BearFramework\App $app)
    {
        $this->app = $app;
    }

    /**
     * Enables the app cache driver. The cached data will be stored in the app data repository.
     * 
     * @return void No value is returned.
     */
    public function useAppDataDriver(): void
    {
        $this->setDriver(new \BearFramework\App\DataCacheDriver($this->app->data));
    }

    /**
     * Enables the null cache driver. No data is stored and no errors are thrown.
     * 
     * @return void No value is returned.
     */
    public function useNullDriver(): void
    {
        $this->setDriver(new \BearFramework\App\NullCacheDriver());
    }

    /**
     * Sets a new cache driver.
     * 
     * @param \BearFramework\App\ICacheDriver $driver The driver to use for cache storage.
     * @return void No value is returned.
     * @throws \Exception
     */
    public function setDriver(\BearFramework\App\ICacheDriver $driver): void
    {
        if ($this->driver !== null) {
            throw new \Exception('A cache driver is already set!');
        }
        $this->driver = $driver;
    }

    /**
     * Returns the cache driver.
     * 
     * @return \BearFramework\App\ICacheDriver
     * @throws \Exception
     */
    private function getDriver(): \BearFramework\App\ICacheDriver
    {
        if ($this->driver !== null) {
            return $this->driver;
        }
        throw new \Exception('No cache driver specified! Use useAppDataDriver() or setDriver() to specify one.');
    }

    /**
     * Constructs a new cache item and returns it.
     * 
     * @var string|null $key The key of the cache item.
     * @var string|null $value The value of the cache item.
     * @return \BearFramework\App\CacheItem Returns a new cache item.
     */
    public function make(string $key = null, $value = null): \BearFramework\App\CacheItem
    {
        if ($this->newCacheItemCache === null) {
            $this->newCacheItemCache = new CacheItem();
        }
        $object = clone($this->newCacheItemCache);
        if ($key !== null) {
            $object->key = $key;
        }
        if ($value !== null) {
            $object->value = $value;
        }
        return $object;
    }

    /**
     * Stores a cache item.
     * 
     * @param \BearFramework\App\CacheItem $item The cache item to store.
     * @return self Returns a reference to itself.
     */
    public function set(CacheItem $item): self
    {
        $driver = $this->getDriver();
        $driver->set($item->key, $item->value, $item->ttl);
        if ($this->hasEventListeners('itemSet')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemSetEvent(clone($item)));
        }
        if ($this->hasEventListeners('itemChange')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemChangeEvent($item->key));
        }
        return $this;
    }

    /**
     * Returns the cache item stored or null if not found.
     * 
     * @param string $key The key of the cache item.
     * @return \BearFramework\App\CacheItem|null The cache item stored or null if not found.
     */
    public function get(string $key): ?\BearFramework\App\CacheItem
    {
        $driver = $this->getDriver();
        $item = null;
        $value = $driver->get($key);
        if ($value !== null) {
            $item = $this->make($key, $value);
        }
        if ($this->hasEventListeners('itemGet')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemGetEvent($key, $item === null ? null : clone($item)));
        }
        if ($this->hasEventListeners('itemRequest')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemRequestEvent($key));
        }
        return $item;
    }

    /**
     * Returns the value of the cache item specified.
     * 
     * @param string $key The key of the cache item.
     * @return mixed The value of the cache item or null if not found.
     */
    public function getValue(string $key)
    {
        $driver = $this->getDriver();
        $value = $driver->get($key);
        if ($this->hasEventListeners('itemGetValue')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemGetValueEvent($key, $value));
        }
        if ($this->hasEventListeners('itemRequest')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemRequestEvent($key));
        }
        return $value;
    }

    /**
     * Returns information whether a key exists in the cache.
     * 
     * @param string $key The key of the cache item.
     * @return bool TRUE if the cache item exists in the cache, FALSE otherwise.
     */
    public function exists(string $key): bool
    {
        $driver = $this->getDriver();
        $exists = $driver->get($key) !== null;
        if ($this->hasEventListeners('itemExists')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemExistsEvent($key, $exists));
        }
        if ($this->hasEventListeners('itemRequest')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemRequestEvent($key));
        }
        return $exists;
    }

    /**
     * Deletes a cache from the cache.
     * 
     * @param string $key The key of the cache item.
     * @return self Returns a reference to itself.
     */
    public function delete(string $key): self
    {
        $driver = $this->getDriver();
        $driver->delete($key);
        if ($this->hasEventListeners('itemDelete')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemDeleteEvent($key));
        }
        if ($this->hasEventListeners('itemChange')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ItemChangeEvent($key));
        }
        return $this;
    }

    /**
     * Deletes all values from the cache.
     */
    public function clear(): \BearFramework\App\CacheRepository
    {
        $driver = $this->getDriver();
        $driver->clear();
        if ($this->hasEventListeners('clear')) {
            $this->dispatchEvent(new \BearFramework\App\Cache\ClearEvent());
        }
        return $this;
    }

}
