<?php
namespace Avh\Utility;

/**
 * Original code by Yoast.
 * This abstract class and it's concrete classes implement defaults and value validation for
 * all Options and subkeys within options.
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
 * [Updating/Adding options]
 * - Use the normal add/update_option() functions. As long a the classes here are instantiated, validation
 * for all options and their subkeys will be automatic.
 * - On (succesfull) update of a couple of options, certain related actions will be run automatically.
 * Some examples:
 * - on change of wpseo[yoast_tracking], the cron schedule will be adjusted accordingly
 * - on change of wpseo_permalinks and wpseo_xml, the rewrite rules will be flushed
 * - on change of wpseo and wpseo_title, some caches will be cleared
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
 * @todo - [JRF => testers] double check that validation will not cause errors when called from upgrade routine
 *       (some of the WP functions may not yet be available)
 */

abstract class OptionsAbstract implements OptionsInterface
{
    /**
     * @var bool Whether the filter extension is loaded
     */
    public static $has_filters = true;
    /**
     * @var object Instance of this class
     */
    protected static $instance;
    /**
     * @var string Option group name for use in settings forms
     *      - will be set automagically if not set in concrete class
     *      (i.e. if it confirm to the normal pattern 'yoast' . $option_name . 'options',
     *      only set in conrete class if it doesn't)
     */
    public $group_name;
    /**
     * @var bool Whether to include the option in the return for WPSEO_Options::get_all().
     *      Also determines which options are copied over for ms_(re)set_blog().
     */
    public $include_in_all = true;
    /**
     * @var bool Whether this option is only for when the install is multisite.
     */
    public $multisite_only = false;
    /**
     * @var array Array of defaults for the option - MUST be set in concrete class.
     *      Shouldn't be requested directly, use $this->getDefaults();
     */
    protected $defaults;
    /**
     * @var string Option name - MUST be set in concrete class and set to public.
     */
    protected $option_name;
    /**
     * @var array Array of variable option name patterns for the option - if any -
     *      Set this when the option contains array keys which vary based on post_type
     *      or taxonomy
     */
    protected $variable_array_key_patterns;

    /**
     * Add all the actions and filters for the option


     */
    protected function __construct()
    {
        self::$has_filters = extension_loaded('filter');

        /* Add filters which get applied to the get_options() results */
        $this->addDefaultFilters(); // return defaults if option not set
        $this->addOptionFilters(); // merge with defaults if option *is* set

        /*
         * The option validation routines remove the default filters to prevent failing to insert an option if it's new. Let's add them back afterwards.
         */
        add_action('add_option', [$this, 'addDefaultFilters']); // adding back after INSERT

        add_action('update_option', [$this, 'addDefaultFilters']);

        /*
         * Make sure the option will always get validated, independently of registerSetting() (only available on back-end)
         */
        add_filter('sanitize_option_' . $this->option_name, [$this, 'validate']);

        /* Register our option for the admin pages */
        add_action('admin_init', [$this, 'registerSetting']);

        /* Set option group name if not given */
        if (!isset($this->group_name) || $this->group_name === '') {
            $this->group_name = 'avh_' . $this->option_name . '_options';
        }

        /* Translate some defaults as early as possible - textdomain is loaded in init on priority 1 */
        add_action('init', [$this, 'handleTranslateDefaults'], 2);

        /**
         * Enrich defaults once custom post types and taxonomies have been registered
         * which is normally done on the init action.
         *
         * @todo - [JRF/testers] verify that none of the options which are only available after
         *       enrichment are used before the enriching
         */
        add_action('init', [$this, 'handleEnrichDefaults'], 99);
    }

// ********** Start  Abstract Methods **********
    /**
     * Clean out old/renamed values within the option
     *
     * @param mixed $option_value
     * @param mixed|null $current_version
     * @param mixed|null $all_old_option_values
     *
     * @return mixed|void
     */
    abstract public function cleanOption($option_value, $current_version = null, $all_old_option_values = null);

    /**
     * Add additional defaults once all post_types and taxonomies have been registered
     *
     * @return void
     */
    abstract public function handleEnrichDefaults();

    /**
     * Translate default values if needed.
     *
     * @return void
     */
    abstract public function handleTranslateDefaults();

    /**
     *  validate all values within the option
     *
     * @param array $dirty
     * @param array $clean
     * @param array $old
     *
     * @return array
     */
    abstract protected function validateOption($dirty, $clean, $old);



// ********** Start Public Methods **********
    /**
     * Add filters to make sure that the option default is returned if the option is not set
     *
     * @return void
     */
    public function addDefaultFilters()
    {
        // Don't change, needs to check for false as could return prio 0 which would evaluate to false
        if (has_filter('default_option_' . $this->option_name, [$this, 'getDefaults']) === false) {
            add_filter('default_option_' . $this->option_name, [$this, 'getDefaults']);
        }
        if (has_filter('default_site_option_' . $this->option_name, [$this, 'getDefaults']) === false) {
            add_filter('default_site_option_' . $this->option_name, [$this, 'getDefaults']);
        }
    }

    /**
     * Add filters to make sure that the option is merged with its defaults before being returned
     *
     * @return void
     */
    public function addOptionFilters()
    {
        // Don't change, needs to check for false as could return prio 0 which would evaluate to false
        if (has_filter('option_' . $this->option_name, [$this, 'getOption']) === false) {
            add_filter('option_' . $this->option_name, [$this, 'getOption']);
        }
        if (has_filter('site_option_' . $this->option_name, [$this, 'getOption']) === false) {
            add_filter('site_option_' . $this->option_name, [$this, 'getOption']);
        }
    }

    /**
     * Retrieve the real old value (unmerged with defaults), clean and re-save the option
     *
     * @param string|null $current_version (optional) Version from which to upgrade, if not set, version specific upgrades will be disregarded
     *
     * @return void
     */
    public function clean($current_version = null)
    {
        $this->removeDefaultFilters();
        $this->removeOptionFilters();
        $option_value = get_option($this->option_name); // = (unvalidated) array, NOT merged with defaults
        $this->addOptionFilters();
        $this->addDefaultFilters();

        $this->import($option_value, $current_version);
    }

    /**
     * Get the enriched default value for an option
     * Checks if the concrete class contains an handleEnrichDefaults() method and if so, runs it.
     *
     * @internal the handleEnrichDefaults method is used to set defaults for variable array keys in an option,
     *           such as array keys depending on post_types and/or taxonomies
     * @return array
     */
    public function getDefaults()
    {
        $this->handleEnrichDefaults();

        return apply_filters('avh_defaults', $this->defaults, $this->option_name);
    }

    /**
     * Merge an option with its default values
     * This method should *not* be called directly!!! It is only meant to filter the getOption() results
     *
     * @param mixed $options Option value
     *
     * @return mixed Option merged with the defaults for that option
     */
    public function getOption($options = null)
    {
        $filtered = $this->arrayFilterMerge($options);

        /*
         * If the option contains variable option keys, make sure we don't remove those settings - even if the defaults are not complete yet. Unfortunately this means we also won't be removing the settings for post types or taxonomies which are no longer in the WP install, but rather that than the other way around
         */
        if (isset($this->variable_array_key_patterns)) {
            $filtered = $this->retainVariableKeys($options, $filtered);
        }

        return $filtered;
    }

    /**
     * Clean and re-save the option
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
     * @param array|boolean $option_value          Option value to be imported
     * @param string|null   $current_version       (optional) Version from which to upgrade, if not set, version specific
     *                                             upgrades will be disregarded
     * @param array|null    $all_old_option_values (optional) Only used when importing old options to have access to the real old values, in contrast to the saved ones
     *
     * @return void
     */
    public function import($option_value, $current_version = null, $all_old_option_values = null)
    {
        if ($option_value === false) {
            $option_value = $this->getDefaults();
        } elseif (is_array($option_value)) {
            $option_value = $this->cleanOption($option_value, $current_version, $all_old_option_values);
        }

        /*
         * Save the cleaned value - validation will take care of cleaning out array keys which should no longer be there
         */
        update_option($this->option_name, $option_value);
    }

    /**
     * Register (whitelist) the option for the configuration pages.
     * The validation callback is already registered separately on the sanitize_option hook,
     * so no need to double register.
     *
     * @return void
     */
    public function registerSetting()
    {
        register_setting($this->group_name, $this->option_name);
    }

    /**
     * Remove the default filters.
     * Called from the validate() method to prevent failure to add new options
     *
     * @return void
     */
    public function removeDefaultFilters()
    {
        remove_filter('default_option_' . $this->option_name, [$this, 'getDefaults']);
        remove_filter('default_site_option_' . $this->option_name, [$this, 'getDefaults']);
    }

    /**
     * Remove the option filters.
     * Called from the clean_up methods to make sure we retrieve the original old option
     *
     * @return void
     */
    public function removeOptionFilters()
    {
        remove_filter('option_' . $this->option_name, [$this, 'getOption']);
        remove_filter('site_option_' . $this->option_name, [$this, 'getOption']);
    }

    /**
     * Validate the option
     *
     * @param mixed $option_value The unvalidated new value for the option
     *
     * @return array Validated new value for the option
     */
    public function validate($option_value)
    {
        $clean = $this->getDefaults();

        /* Return the defaults if the new value is empty */
        if (!is_array($option_value) || $option_value === []) {
            return $clean;
        }

        $option_value = array_map([__CLASS__, 'trim_recursive'], $option_value);
        $old = get_option($this->option_name);
        $clean = $this->validateOption($option_value, $clean, $old);

        /* Retain the values for variable array keys even when the post type/taxonomy is not yet registered */
        if (isset($this->variable_array_key_patterns)) {
            $clean = $this->retainVariableKeys($option_value, $clean);
        }

        $this->removeDefaultFilters();

        return $clean;
    }



// **********  Start Protected Methods **********
    /**
     * Helper method - Combines a fixed array of default values with an options array
     * while filtering out any keys which are not in the defaults array.
     *
     * @todo [JRF] - shouldn't this be a straight array merge ? at the end of the day, the validation
     *       removes any invalid keys on save
     *
     * @param array|null $options (Optional) Current options
     *                       - if not set, the option defaults for the $option_key will be returned.
     *
     * @return array Combined and filtered options array.
     */
    protected function arrayFilterMerge($options = null)
    {
        $defaults = $this->getDefaults();

        if (!isset($options) || $options === false) {
            return $defaults;
        }

        $options = (array) $options;
        $filtered = array_merge($defaults, $options);

        return $filtered;
    }

    /**
     * Check whether a given array key conforms to one of the variable array key patterns for this option
     *
     * @usedby validateOption() methods for options with variable array keys
     *
     * @param string $key Array key to check
     *
     * @return string Pattern if it conforms, original array key if it doesn't or if the option
     *         does not have variable array keys
     */
    protected function getSwitchKey($key)
    {
        if (!isset($this->variable_array_key_patterns) || (!is_array($this->variable_array_key_patterns) || $this->variable_array_key_patterns === [])
        ) {
            return $key;
        }

        foreach ($this->variable_array_key_patterns as $pattern) {
            if (strpos($key, $pattern) === 0) {
                return $pattern;
            }
        }

        return $key;
    }

    /**
     * Make sure that any set option values relating to post_types and/or taxonomies are retained,
     * even when that post_type or taxonomy may not yet have been registered.
     *
     * @internal The wpseo_titles concrete class overrules this method. Make sure that any changes
     *           applied here, also get ported to that version.
     *
     * @param array $dirty Original option as retrieved from the database
     * @param array $clean Filtered option where any options which shouldn't be in our option have already been
     *                     removed and any options which were not set have been set to their defaults
     *
     * @return array
     */
    protected function retainVariableKeys($dirty, $clean)
    {
        if ((is_array($this->variable_array_key_patterns) && $this->variable_array_key_patterns !== []) && (is_array($dirty) && $dirty !== [])
        ) {
            foreach ($dirty as $key => $value) {
                foreach ($this->variable_array_key_patterns as $pattern) {
                    if (strpos($key, $pattern) === 0 && !isset($clean[$key])) {
                        $clean[$key] = $value;
                        break;
                    }
                }
            }
        }

        return $clean;
    }
}
