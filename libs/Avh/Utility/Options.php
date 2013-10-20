<?php
namespace Avh\Utility;

use Avh\Utility\Common;

/**
 * This class is based of the options class of the scbFramework
 */
final class Options
{

    protected $option_key; // the option name
    protected $option_defaults; // the default values

    // prevent directly access.
    public function __construct()
    {
    }

    // prevent clone.
    public function __clone()
    {
    }

    /**
     * Create a new set of options
     *
     * @param string $key
     *            Option name
     * @param string $file
     * @param array  $defaults
     *            An associative array of default values (optional)
     */
    public function load($option_name, $file, $defaults = array())
    {
        $this->option_key = $option_name;
        $this->option_defaults = $defaults;

        if ($file) {
            register_activation_hook($file, array($this, 'handleActionActivate'));
            Common::addUninstallHook($file, array('Options', 'delete'));
        }
    }

    /**
     * Get option values for one, many or all fields
     *
     * @param string|array $field
     *            The field(s) to get
     * @return mixed Whatever is in those fields
     */
    public function getOptions($field = '')
    {
        $_data = get_option($this->option_key, false);
        if (false === $_data) {
            $_data = array_merge($this->option_defaults, $_data);
        }

        return $this->get($field, $_data);
    }

    /**
     * Set all data fields, certain fields or a single field
     *
     * @param string|array $field
     *            The field to update or an associative array
     * @param mixed $value
     *            The new value ( ignored if $field is array )
     * @return null
     */
    public function setOptions($field, $value = '')
    {
        if (is_array($field)) {
            $_newdata = $field;
        } else {
            $_newdata = array($field => $value);
        }

        $this->update($_newdata);
    }

    /**
     * Reset option to defaults
     *
     * @return null
     */
    public function resetOptions()
    {
        $this->update($this->option_defaults);
    }

    /**
     * Remove any keys that are not in the defaults array
     */
    public function cleanupOptions()
    {
        $_data = $this->getOptions();
        $_data = $this->clean($_data);
        update_option($this->option_key, $_data);
    }

    /**
     * Update raw data
     *
     * @param mixed $newdata
     */
    private function update($newdata)
    {
        $_all_data = array_merge($this->getOptions(), $newdata);
        update_option($this->option_key, $_all_data);
    }

    /**
     * Delete the option
     */
    public function deleteOptions()
    {
        delete_option($this->option_key);
    }

    /**
     * Add the options to the WordPress DB
     */
    public function handleActionActivate()
    {
        add_option($this->option_key, $this->option_defaults);
    }

    /**
     * Keep only the keys defined in $this->defaults
     *
     * @param  array $data
     * @return array
     */
    private function clean($data)
    {
        return wp_array_slice_assoc($data, array_keys($this->option_defaults));
    }

    /**
     * Get one, more or all fields from an array
     *
     * @param  string|array $field
     * @param  array        $data
     * @return mixed
     */
    private function get($field, $data)
    {
        if (empty($field)) {
            return $data;
        }

        if (is_string($field)) {
            return $data[$field];
        }

        foreach ($field as $key) {
            if (isset($data[$key])) {
                $_result[] = $data[$key];
            }
        }

        return $_result;
    }
}
