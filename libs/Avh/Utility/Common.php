<?php
namespace Avh\Utility;

final class Common
{

    /**
     * Returns the wordpress version
     * Note: 2.7.x will return 2.7
     *
     * @return float
     */
    public static function getWordpressVersion()
    {
        static $_version = null;
        if (!isset($_version)) {
            // Include WordPress version
            require (ABSPATH . WPINC . '/version.php');
            $_version = (float) $wp_version;
        }

        return $_version;
    }

    /**
     * Determines if the current version of PHP is greater then the supplied value
     *
     * @param $version string
     *            Defaults to 5.0.0
     * @return bool
     */
    public static function isPHP($version = '5.0.0')
    {
        static $_is_php = null;
        $version = (string) $version;
        if (!isset($_is_php[$version])) {
            $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? false : true;
        }

        return $_is_php[$version];
    }

    /**
     * Get the base directory of a directory structure
     *
     * @param  string $directory
     * @return string
     *
     */
    public static function getBaseDirectory($directory)
    {
        // get public directory structure eg "/top/second/third"
        $_public_directory = dirname($directory);
        // place each directory into array
        $_directory_array = explode('/', $_public_directory);
        // get highest or top level in array of directory strings
        $_public_base = end($_directory_array);

        return $_public_base;
    }

    /**
     * This function will take an IP address or IP number in almost any format (that I can think of) and will return
     * it's decimal unsigned equivalent, as a string.
     * Kind				=> Input				=> Return		=> long2ip(Return)
     * DottedQuadDec	=> 192.168.255.109		=> 3232300909	=> 192.168.255.109
     * PosIntStr		=> 3232300909			=> 3232300909	=> 192.168.255.109
     * NegIntStr		=> -1062666387			=> 3232300909	=> 192.168.255.109
     * PosInt			=> 3232300909			=> 3232300909	=> 192.168.255.109
     * NegInt			=> -1062666387			=> 3232300909	=> 192.168.255.109
     * DottedQuadHex	=> 0xc0.0xA8.0xFF.0x6D	=> 0	=> 0.0.0.0
     * DottedQuadOct	=> 0300.0250.0377.0155	=> 0	=> 0.0.0.0
     * HexIntStr		=> 0xC0A8FF6D			=> 0	=> 0.0.0.0
     * HexInt			=> 3232300909 => 3232300909	=> 192.168.255.109
     *
     * @param string|numeric $ip
     */
    public static function getIp2long($ip)
    {
        if (is_numeric($ip)) {
            $_return = sprintf("%u", floatval($ip));
        } else {
            $_return = sprintf("%u", floatval(ip2long($ip)));
        }

        return $_return;
    }

    /**
     * Allows for the use of more than one uninstall hook.
     * WordPress only allows one callback for the uninstall hook.
     * The AVH_Options class needs one and you probably need more wehn writing a plugin.
     *
     * @param string   $file
     * @param callback $callback
     *            The callback to run when the hook is called. Must be a static method or function.
     */
    public static function addUninstallHook($file, $callback)
    {
        if (is_array($callback) && is_object($callback[0])) {
            _doing_it_wrong(__FUNCTION__, __('Only a static class method or function can be used in an uninstall hook.'), '3.1');

            return;
        }
        register_uninstall_hook($file, '__return_false'); // dummy
        add_action('avh_uninstall_' . plugin_basename($file), $callback);
    }

    public static function doUninstall($plugin)
    {
        do_action('avh_uninstall_' . plugin_basename($plugin));
    }
}
