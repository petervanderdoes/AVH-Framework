<?php
namespace Avh\Utility;

/**
 * Interface OptionsInterface
 *
 * @package Avh\Utility
 */
interface OptionsInterface
{
    /**
     * Add filters to make sure that the option is merged with its defaults before being returned
     *
     * @return void
     */
    public function addOptionFilters();

    /**
     * Retrieve the real old value (unmerged with defaults), clean and re-save the option
     *
     * @param string|null $current_version (optional) Version from which to upgrade.
     *                                     if not set, version specific upgrades will be disregarded
     *
     * @return void
     */
    public function clean($current_version = null);

    /**
     * Clean out old/renamed values within the option
     *
     * @param mixed $option_value
     * @param mixed $current_version
     * @param mixed $all_old_option_values
     *
     * @return mixed|void
     */
    public function cleanOption($option_value, $current_version = null, $all_old_option_values = null);

    /**
     * Get the enriched default value for an option
     * Checks if the concrete class contains an handleEnrichDefaults() method and if so, runs it.
     *
     * @internal the handleEnrichDefaults method is used to set defaults for variable array keys in an option,
     *           such as array keys depending on post_types and/or taxonomies
     * @return array
     */
    public function getDefaults();

    /**
     * Merge an option with its default values
     * This method should *not* be called directly!!! It is only meant to filter the getOption() results
     *
     * @param mixed $options Option value
     *
     * @return mixed Option merged with the defaults for that option
     */
    public function getOption($options = null);

    /**
     * Add additional defaults once all post_types and taxonomies have been registered
     */
    public function handleEnrichDefaults();

    /**
     * Translate default values if needed.
     */
    public function handleTranslateDefaults();

    /**
     * Clean and re-save the option
     *
     * @param array|boolean $option_value          Option value to be imported
     * @param string|null   $current_version       (optional) Version from which to upgrade, if not set, version specific
     *                                             upgrades will be disregarded
     * @param array|null    $all_old_option_values (optional) Only used when importing old options to have access to the real old values, in contrast to the saved ones
     *
     * @return void
     */
    public function import($option_value, $current_version = null, $all_old_option_values = null);

    /**
     * Register (whitelist) the option for the configuration pages.
     * The validation callback is already registered separately on the sanitize_option hook,
     * so no need to double register.
     *
     * @return void
     */
    public function registerSetting();

    /**
     * Remove the default filters.
     * Called from the validate() method to prevent failure to add new options
     *
     * @return void
     */
    public function removeDefaultFilters();

    /**
     * Remove the option filters.
     * Called from the clean_up methods to make sure we retrieve the original old option
     *
     * @return void
     */
    public function removeOptionFilters();

    /**
     * Validate the option
     *
     * @param mixed $option_value The unvalidated new value for the option
     *
     * @return array Validated new value for the option
     */
    public function validate($option_value);
}
