<?php
namespace Avh\Controller;

use Avh\Utility\Common;

/**
 * AVH_UR_PluginController
 *
 * A parent class for WordPress plugins.
 * Author: Peter van der Does
 * Original Author: Emmanuel GEORJON
 */

/**
 * Class PluginController
 *
 * Provide some functions to create a WordPress plugin
 */
class PluginController
{

    protected $requirements_error_msg = '';

    protected $update_notice = '';

    protected $options_page_id = '';

    protected $pages = array();

    protected $hooks = array();

    protected $tinyMCE_buttons = array();

    protected $pluginfile = '';

    protected $textdomain = '';

    protected $settings;

    protected $classes;

    protected $options;

    public static $base_url;

    /**
     * Class contructor
     *
     * @return object
     */
    public function __construct($settings, $options)
    {
        $this->settings = $settings;
        $this->options = $options;

        // Move some of the saved settings to local, this makes things easier to read and probably speed things up as
        // well.
        $this->pluginfile = $this->settings->plugin_file;
        $this->textdomain = $this->settings->text_domain;
        self::$base_url = $this->settings->plugin_url;
    }

    /**
     * Class destructor
     *
     * @return boolean true
     */
    public function __destruct()
    {
        // Nothing
    }

    /**
     * Sets up basic plugin needs.
     *
     * @action init
     */
    public function actionInit()
    {
        if (!isset($this->textdomain)) {
            load_plugin_textdomain($this->textdomain, false, $this->settings->plugin_dir . '/lang');
        }

        // Register Styles and Scripts

        $style = $this->getStyleName();
        wp_register_style($style . '-css', $this->settings->plugin_url . '/css/' . $style . '.css', array(), $this->settings->plugin_version, 'screen');
    }

    /**
     * Runs on register_activation_hook
     */
    public function installPlugin()
    {
    }

    /**
     * Gets the style name.
     *
     * @param string $style
     *            If left empty the style name resolves to admin or public, depending on whether the function
     *            is called while in admin
     * @return string
     */
    protected function getStyleName($style = '')
    {
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
            $minified = '';
        } else {
            $minified = '.min';
        }
        if (empty($style)) {
            if (is_admin()) {
                $full_style_name = $this->settings->file_prefix . 'admin' . $minified;
            } else {
                $full_style_name = $this->settings->file_prefix . 'public' . $minified;
            }
        } else {
            $full_style_name = $this->settings->file_prefix . $style . $minified;
        }

        return $full_style_name;
    }

    /**
     * Gets the javascript name.
     *
     * @param string $style
     *            If left empty the style name resolves to admin or public, depending on whether the function
     *            is called while in admin
     * @return string
     */
    protected function getJsName($script = '')
    {
        $minified = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.closure';
        if (empty($script)) {
            if (is_admin()) {
                $full_script_name = $this->settings->file_prefix . 'admin' . $minified;
            } else {
                $full_script_name = $this->settings->file_prefix . 'public' . $minified;
            }
        } else {
            $full_script_name = $this->settings->file_prefix . $script . $minified;
        }

        return $full_script_name;
    }

    public function deactivation()
    {
    }

    /**
     * Called to start the plugin.
     */
    public function load()
    {
        add_action('init', array($this, 'actionInit'));

        if (is_admin()) {
            register_deactivation_hook($this->pluginfile, array($this, 'deactivation'));
            register_activation_hook($this->pluginfile, array($this, 'installPlugin'));
            add_action('in_plugin_update_message-' . basename($this->pluginfile), array($this, 'actionInPluginUpdateMessage'));
        }
    }

    /**
     * setUpdateNotice
     *
     * @param string $msg
     */
    public function setUpdateNotice($msg)
    {
        $this->update_notice = $msg;
    }

    /**
     * This function is called when there's an update of the plugin available @ WordPress
     */
    public function actionInPluginUpdateMessage()
    {
        $response = wp_remote_get($this->settings->plugin_readme_url, array('user-agent' => 'WordPress/' . Common::getWordpressVersion() . ' ' . $this->settings->plugin_name . '/' . $this->settings->plugin_version));
        if (!is_wp_error($response) || is_array($response)) {
            $response_data = $response['body'];
            $matches = null;
            if (preg_match('~==\s*Changelog\s*==\s*=\s*Version\s*[0-9.]+\s*=(.*)(=\s*Version\s*[0-9.]+\s*=|$)~Uis', $response_data, $matches)) {
                $changelog = (array) preg_split('~[\r\n]+~', trim($matches[1]));
                $prev_version = null;
                preg_match('([0-9.]+)', $matches[2], $prev_version);
                echo '<div style="color: #f00;">What\'s new in this version:</div><div style="font-weight: normal;">';
                $ul = false;
                foreach ($changelog as $index => $line) {
                    if (preg_match('~^\s*\*\s*~', $line)) {
                        if (!$ul) {
                            echo '<ul style="list-style: disc; margin-left: 20px;">';
                            $ul = true;
                        }
                        $line = preg_replace('~^\s*\*\s*~', '', htmlspecialchars($line));
                        echo '<li style="width: 50%; margin: 0; float: left; ' . ($index % 2 == 0 ? 'clear: left;' : '') . '">' . $line . '</li>';
                    } else {
                        if ($ul) {
                            echo '</ul><div style="clear: left;"></div>';
                            $ul = false;
                        }
                        echo '<p style="margin: 5px 0;">' . htmlspecialchars($line) . '</p>';
                    }
                }
                if ($ul) {
                    echo '</ul><div style="clear: left;"></div>';
                }
                if ($prev_version[0] != $this->settings->plugin_version) {
                    echo '<div style="color: #f00; font-weight: bold;">';
                    echo '<br />';
                    echo sprintf(__('The installed version, %s, is more than one version behind.', $this->textdomain), $this->settings->plugin_version);
                    echo '<br />';
                    echo __('More changes have been made since the currently installed version, consider checking the changelog.', $this->textdomain);
                    echo '</div><div style="clear: left;"></div>';
                }
                echo '</div>';
            }
        }
    }

    /**
     * Display a specific message in the plugin update message.
     */
    public function displayPluginUpdateNotice()
    {
        if ($this->update_notice != '') {
            echo '<span class="spam">' . strip_tags(__($this->update_notice, $this->textdomain), '<br><a><b><i><span>') . '</span>';
        }
    }

    /**
     * Add a "settings" link to access to the option page from the plugin list
     *
     * @param string $links
     * @return none
     */
    public function filterPluginActions($links)
    {
        if ($this->options_page_id != '') {
            $settings_link = '<a href="' . admin_url('options-general.php?page=' . $this->options_page_id) . '">' . __('Settings') . '</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    /**
     * Display a metabox-like in admin interface.
     *
     * @param string $id
     *            string
     * @param string $title
     * @param string $content
     */
    public function displayBox($id, $title, $content)
    {
        echo '<div id="' . $id . '" class="postbox">';
        echo '<div class="handlediv" title="Click to toggle">';
        echo '<br />';
        echo '</div>';
        echo '<h3 class="hndle">';
        echo '<span>';
        _e($title, $this->textdomain);
        echo '</span>';
        echo '</h3>';
        echo '<div class="inside">';
        _e($content, $this->textdomain);
        echo '</div>';
        echo '</div>';
    }

    /**
     * Add a menu and a page
     */
    public function addPage(array $args)
    {

        // @formatter:off
        $default_args = array(
            'id' => '',
            'parent_id' => '',
            'type' => 'options',
            'page_title' => $this->settings->plugin_name . __(' settings', $this->textdomain),
            'menu_title' => $this->settings->plugin_name,
            'access_level' => 'manage_options',
            'display_callback' => '',
            'option_link' => false,
            'load_callback' => false,
            'load_scripts' => false,
            'shortname' => null,
            'icon_url' => null,
            'position' => null
        );
        // @formatter:on

        $values = wp_parse_args($args, $default_args);
        if ($values['id'] != '' && $values['display_callback'] != '') {
            $this->pages[$values['id']] = $values;
            if ($values['option_link']) {
                $this->options_page_id = $values['id'];
            }

            return ($values['id']);
        } else {
            return (false);
        }
    }

    /**
     * Run the admin menu hook
     */
    public function adminMenu()
    {

        // Add a new submenu under Options:
        if (sizeof($this->pages) > 0) {
            foreach ($this->pages as $id => $page) {

                // Create the menu
                if ($page['type'] == 'menu') {
                    $hook = add_menu_page(__($page['page_title'], $this->textdomain), __($page['menu_title'], $this->textdomain), $page['access_level'], $id, array($this, $page['display_callback']), $page['icon_url'], $page['position']);
                } else {
                    $hook = $this->adminMenuSubMenu($page);
                }

                // Get the hook of the page
                $this->hooks[$page['display_callback']][$id] = $hook;

                // Add load, and print_scripts functions (attached to the hook)
                if ($page['load_callback'] !== false) {
                    add_action('load-' . $hook, array($this, $page['load_callback']));
                }
                if ($page['load_scripts'] !== false) {
                    add_action('admin_print_scripts-' . $hook, array($this, $page['load_scripts']));
                }

                // Add the link into the plugin page
                if ($this->options_page_id == $id) {
                    add_filter('plugin_action_links_' . plugin_basename($this->pluginfile), array($this, 'filterPluginActions'));
                }
            }
            unset($this->pages);
        }
    }

    /**
     * Add a submenu
     *
     * @param array $page
     * @param array $page_list
     * @return Ambigous <string, boolean>
     */
    private function adminMenuSubMenu($page)
    {
        // @formatter:off
        $page_list = array(
            'dashboard' => 'index.php',
            'posts' => 'edit.php',
            'options' => 'options-general.php',
            'settings' => 'options-general.php',
            'tools' => 'tools.php',
            'theme' => 'themes.php',
            'users' => 'users.php',
            'media' => 'upload.php',
            'links' => 'link-manager.php',
            'pages' => 'edit.php?post_type=page',
            'comments' => 'edit-comments.php'
        );
        // @formatter:on

        if ($page['type'] != 'submenu') {
            $page['parent_id'] = $page_list[$page['type']];
        }

        $hook = add_submenu_page($page['parent_id'], __($page['page_title'], $this->textdomain), __($page['menu_title'], $this->textdomain), $page['access_level'], $id, array($this, $page['display_callback']));

        if (isset($this->pages[$page['parent_id']]) && $this->pages[$page['parent_id']]['shortname'] != '') {
            global $submenu;
            $submenu[$page['parent_id']][0][0] = $this->pages[$page['parent_id']]['shortname'];
            $this->pages[$page['parent_id']]['shortname'] = '';
        }
        return $hook;
    }

    /**
     * Returns the pagehook name
     *
     * @param string $page_id
     * @param string $function
     * @return mixed If pagehook does not exists return false other return the page hook name
     */
    protected function getPageHook($page_id = '', $function = '')
    {
        if ($page_id == '' || $function == '') {
            return false;
        } else {
            return (isset($this->hooks[$function][$page_id]) ? $this->hooks[$function][$page_id] : false);
        }
    }

    /**
     * Adds the given capability to the given role
     *
     * @param string $capability
     * @param string $role
     */
    protected function addCapability($capability, $role)
    {
        $role_object = get_role($role);
        if ($role_object != null && !$role_object->has_cap($capability)) {
            $role_object->add_cap($capability);
        }
    }

    /**
     * Add a TinyMCE button
     *
     * @param string $button_name
     */
    public function addTinyMceButton($button_name, $tinymce_plugin_path, $js_file_name = 'editor_plugin.js')
    {
        $index = sizeof($this->tinyMCE_buttons);
        $this->tinyMCE_buttons[$index]->name = $button_name;
        $this->tinyMCE_buttons[$index]->js_file = $js_file_name;
        $this->tinyMCE_buttons[$index]->path = $tinymce_plugin_path;
    }

    /**
     * Insert button in wordpress post editor
     *
     * @param array $buttons
     * @return array
     */
    public function registerButton($buttons)
    {
        foreach ($this->tinyMCE_buttons as $value) {
            array_push($buttons, $value->name);
        }

        return $buttons;
    }

    /**
     * Load the TinyMCE plugin : editor_plugin.js
     *
     * @param array $plugin_array
     * @return $plugin_array
     */
    public function addTinyMcePlugin(array $plugin_array)
    {
        foreach ($this->tinyMCE_buttons as $value) {
            $plugin_array[$value->name] = $this->settings->plugin_url . $value->path . '/' . $value->js_file;
        }

        return $plugin_array;
    }

    public function tinyMceVersion($version)
    {
        return ++$version;
    }
}
