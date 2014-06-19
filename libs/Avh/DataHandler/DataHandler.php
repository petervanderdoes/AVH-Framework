<?php

namespace Avh\DataHandler;

use Avh\DataHandler\AttributeBagInterface;

class DataHandler
{
    protected $registry;

    /**
     * Class constructor

     *
*@param AttributeBagInterface $registry
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
    public function get($key)
    {
        return $this->registry->get($key);
    }

    /**
     * Save data to the registry
     */
    public function set($key, $value)
    {
        $this->registry->set($key, $value);

        return $this;
    }
}