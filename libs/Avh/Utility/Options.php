<?php
namespace Avh\Utility;

use Avh\Utility\Common;

/**
 * This class is based of the options class of the scbFramework
 */
final class Options
{

    private $option_key; // the option name
    private $option_defaults; // the default values
    private $settings;

    // prevent directly access.
    public function __construct($settings)
    {
        $this->settings = $settings;
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
     * @param string $file The PLUGINNAME
     * @param array  $defaults
     *            An associative array of default values (optional)
     */
    public function load($option_name, $defaults = array())
    {
        $this->option_key = $option_name;
        $this->option_defaults = $defaults;

        register_activation_hook($this->settings->plugin_file, array($this, 'handleActionActivate'));
        Common::addUninstallHook($this->settings->plugin_file, array('Options', 'delete'));
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
        $data = get_option($this->option_key, false);
        if (false === $data) {
            $data = array_merge($this->option_defaults, $data);
        }

        return $this->get($field, $data);
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
            $new_data = $field;
        } else {
            $new_data = array($field => $value);
        }

        $this->update($new_data);
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
        $data = $this->getOptions();
        $data = $this->clean($data);
        update_option($this->option_key, $data);
    }

    /**
     * Update raw data
     *
     * @param mixed $newdata
     */
    private function update($newdata)
    {
        $all_data = array_merge($this->getOptions(), $newdata);
        update_option($this->option_key, $all_data);
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
                $result[] = $data[$key];
            }
        }

        return $result;
    }
}