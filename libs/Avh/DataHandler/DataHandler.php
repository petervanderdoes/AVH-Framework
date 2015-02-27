<?php
namespace Avh\DataHandler;

/**
 * Class DataHandler
 *
 * @package Avh\DataHandler
 */
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
     * Get all data
     *
     * @return array
     */
    public function all()
    {
        return $this->registry->all();
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
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->registry->get($key, $default);
    }

    /**
     * Chekc if key exist in registry
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->registry->has($key);
    }

    /**
     * Save data to the registry
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->registry->set($key, $value);

        return $this;
    }
}
