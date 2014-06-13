<?php
namespace Avh\Utility;


abstract class SettingsAbstract
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
            $return = $this->settings[$key];
        } else {
            $return = null;
        }

        return $return;
    }

    public function __set($key, $data)
    {
        $this->settings[$key] = $data;
    }

    public function __isset($key)
    {
        return isset($this->settings[$key]);
    }

    public function __unset($key)
    {
        if (isset($this->settings[$key])) {
            unset($this->settings[$key]);
        }
    }
}
