<?php
namespace Avh\DataHandler;

interface RegistrableInterface
{
    /**
     * Clear the array registry
     *
     * @return void
     */
    public function clear();

    /**
     * Get the specified value from the array registry
     *
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function get($key);

    /**
     * Save the specified value to the array registry
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function set($key, $value);
}