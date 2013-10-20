<?php
namespace Avh\Utility;

abstract class Settings
{

    /**
     * Our array of settings
     *
     * @access protected
     */
    private $settings = array();

    public function __get($key)
    {
        if (isset($this->settings[$key])) {
            $_return = $this->settings[$key];
        } else {
            $_return = null;
        }

        return $_return;
    }

    public function __set($key, $data)
    {
        $this->settings[$key] = $data;
    }

    public function __unset($key)
    {
        if (isset($this->settings[$key])) {
            unset($this->settings[$key]);
        }
    }

    public function __isset($key)
    {
        return isset($this->settings[$key]);
    }
}
