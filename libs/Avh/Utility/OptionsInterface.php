<?php
/**
 * Created by PhpStorm.
 * User: pdoes
 * Date: 6/10/14
 * Time: 7:52 PM
 */
namespace Avh\Utility;


// ---------- Private methods ----------
/**
 * Original code by Yoast.
 *
 * This abstract class and it's concrete classes implement defaults and value validation for
 * all Options and subkeys within options.
 *
 * Some guidelines:
 * [Retrieving options]
 * - Use the normal get_option() to retrieve an option. You will receive a complete array for the option.
 * Any subkeys which were not set, will have their default values in place.
 * - In other words, you will normally not have to check whether a subkey isset() as they will *always* be set.
 * They will also *always* be of the correct variable type.
 * The only exception to this are the options with variable option names based on post_type or taxonomy
 * as those will not always be available before the taxonomy/post_type is registered.
 * (they will be available if a value was set, they won't be if it wasn't as the class won't know
 * that a default needs to be injected).
 * Oh and the very few options where the default value is null, i.e. wpseo->'theme_has_description'
 *
 * [Updating/Adding options]
 * - Use the normal add/update_option() functions. As long a the classes here are instantiated, validation
 * for all options and their subkeys will be automatic.
 * - On (succesfull) update of a couple of options, certain related actions will be run automatically.
 * Some examples:
 * - on change of wpseo[yoast_tracking], the cron schedule will be adjusted accordingly
 * - on change of wpseo_permalinks and wpseo_xml, the rewrite rules will be flushed
 * - on change of wpseo and wpseo_title, some caches will be cleared
 *
 *
 * [Important information about add/updating/changing these classes]
 * - Make sure that option array key names are unique across options. The Options::get_all()
 * method merges most options together. If any of them have non-unique names, even if they
 * are in a different option, they *will* overwrite each other.
 * - When you add a new array key in an option: make sure you add proper defaults and add the key
 * to the validation routine in the proper place or add a new validation case.
 * You don't need to do any upgrading as any option returned will always be merged with the
 * defaults, so new options will automatically be available.
 * If the default value is a string which need translating, add this to the concrete class
 * handleTranslateDefaults() method.
 * - When you remove an array key from an option: if it's important that the option is really removed,
 * add the Option::clean_up( $option_name ) method to the upgrade run.
 * This will re-save the option and automatically remove the array key no longer in existance.
 * - When you rename a sub-option: add it to the cleanOption() routine and run that in the upgrade run.
 * - When you change the default for an option sub-key, make sure you verify that the validation routine will
 * still work the way it should.
 * Example: changing a default from '' (empty string) to 'text' with a validation routine with tests
 * for an empty string will prevent a user from saving an empty string as the real value. So the
 * test for '' with the validation routine would have to be removed in that case.
 * - If an option needs specific actions different from defined in this abstract class, you can just overrule
 * a method by defining it in the concrete class.
 *
 *
 * @todo - [JRF => testers] double check that validation will not cause errors when called from upgrade routine
 *       (some of the WP functions may not yet be available)
 */
interface OptionsInterface
{

// ---------- Public methods ----------
    /**
     * Add filters to make sure that the option default is returned if the option is not set
     *
     * @return void
     */
    public function addDefaultFilters();

    /**
     * Add filters to make sure that the option is merged with its defaults before being returned
     *
     * @return void
     */
    public function addOptionFilters();

    /**
     * Retrieve the real old value (unmerged with defaults), clean and re-save the option
     *
     * @uses import()
     *
     * @param string $current_version
     *            (optional) Version from which to upgrade, if not set,
     *            version specific upgrades will be disregarded
     *
     * @return void
     */
    public function clean($current_version = null);

    /**
     * Clean out old/renamed values within the option
     */
    public function cleanOption($option_value, $current_version = null, $all_old_option_values = null);

    /**
     * Add additional defaults once all post_types and taxonomies have been registered
     */
    public function handleEnrichDefaults();

    /**
     * Get the enriched default value for an option
     *
     * Checks if the concrete class contains an handleEnrichDefaults() method and if so, runs it.
     *
     * @internal the handleEnrichDefaults method is used to set defaults for variable array keys in an option,
     *           such as array keys depending on post_types and/or taxonomies
     *
     * @return array
     */
    public function getDefaults();

    /**
     * Merge an option with its default values
     *
     * This method should *not* be called directly!!! It is only meant to filter the getOption() results
     *
     * @param mixed $options
     *            Option value
     *
     * @return mixed Option merged with the defaults for that option
     */
    public function getOption($options = null);

    /**
     * Clean and re-save the option
     *
     * @uses cleanOption() method from concrete class if it exists
     *
     * @todo [JRF/whomever] Figure out a way to show settings error during/after the upgrade - maybe
     *       something along the lines of:
     *       -> add them to a property in this class
     *       -> if that property isset at the end of the routine and add_settings_error function does not exist,
     *       save as transient (or update the transient if one already exists)
     *       -> next time an admin is in the WP back-end, show the errors and delete the transient or only delete it
     *       once the admin has dismissed the message (add ajax function)
     *       Important: all validation routines which add_settings_errors would need to be changed for this to work
     *
     * @param array  $option_value
     *            Option value to be imported
     * @param string $current_version
     *            (optional) Version from which to upgrade, if not set,
     *            version specific upgrades will be disregarded
     * @param array  $all_old_option_values
     *            (optional) Only used when importing old options to have
     *            access to the real old values, in contrast to the saved ones
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
     * Translate default values if needed.
     */
    public function handleTranslateDefaults();

    /**
     * Validate the option
     *
     * @param mixed $option_value
     *            The unvalidated new value for the option
     *
     * @return array Validated new value for the option
     */
    public function validate($option_value);
}