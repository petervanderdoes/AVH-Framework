<?php
namespace Avh\DataHandler;

class ArrayRegistry implements RegistrableInterface
{
    protected $data = array();

    /**
     * Clear the array registry
     */
    public function clear()
    {
        $this->data = array();
    }

    /**
     * Get the specified value from the array registry
     *
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        $key = strtolower($key);

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Save the specified value to the array registry
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->data[strtolower($key)] = $value;
    }
}
