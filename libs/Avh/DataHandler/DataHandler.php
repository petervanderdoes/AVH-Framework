<?php

namespace Avh\DataHandler;

class DataHandler
{
    protected $registry;

    /**
     * Class constructor
     *
     * @param AttributeBagInterface $registry
     */
    public function __construct(AttributeBagInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Clear the registry
     */
    public function clear()
    {
        $this->registry->clear();
    }

    /**
     * Get data from the registry
     */
    public function get($key, $default = null)
    {
        return $this->registry->get($key, $default);
    }

    /**
     * Save data to the registry
     */
    public function set($key, $value)
    {
        $this->registry->set($key, $value);

        return $this;
    }

    public function has($key)
    {
        return $this->registry->has($key);
    }
}