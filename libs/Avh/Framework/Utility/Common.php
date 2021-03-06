<?php
namespace Avh\Framework\Utility;

/**
 * Class Common
 *
 * @package Avh\Framework\Utility
 */
final class Common
{
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
            _doing_it_wrong(__FUNCTION__,
                            __('Only a static class method or function can be used in an uninstall hook.'),
                            '3.1');

            return;
        }
        register_uninstall_hook($file, '__return_false'); // dummy
        add_action('avh_uninstall_' . plugin_basename($file), $callback);
    }

    /**
     * Clears the WP or W3TC cache depending on which is used.
     *
     * @static
     * @return void
     */
    public static function clearCache()
    {
        if (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        } elseif (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
    }

    /**
     * Uninstall the plugin
     *
     * @param string $plugin
     */
    public static function doUninstall($plugin)
    {
        do_action('avh_uninstall_' . plugin_basename($plugin));
    }

    /**
     * Get the base directory of a directory structure
     *
     * @param  string $directory
     *
     * @return string
     */
    public static function getBaseDirectory($directory)
    {
        // get public directory structure eg "/top/second/third"
        $public_directory = dirname($directory);
        // place each directory into array
        $directory_array = explode('/', $public_directory);
        // get highest or top level in array of directory strings
        $_public_base = end($directory_array);

        return $_public_base;
    }

    /**
     * This function will take an IP address or IP number in almost any format (that I can think of) and will return
     * it's decimal unsigned equivalent, as a string.
     * Kind                => Input                => Return        => long2ip(Return)
     * DottedQuadDec    => 192.168.255.109        => 3232300909    => 192.168.255.109
     * PosIntStr        => 3232300909            => 3232300909    => 192.168.255.109
     * NegIntStr        => -1062666387            => 3232300909    => 192.168.255.109
     * PosInt            => 3232300909            => 3232300909    => 192.168.255.109
     * NegInt            => -1062666387            => 3232300909    => 192.168.255.109
     * DottedQuadHex    => 0xc0.0xA8.0xFF.0x6D    => 0    => 0.0.0.0
     * DottedQuadOct    => 0300.0250.0377.0155    => 0    => 0.0.0.0
     * HexIntStr        => 0xC0A8FF6D            => 0    => 0.0.0.0
     * HexInt            => 3232300909 => 3232300909    => 192.168.255.109
     *
     * @param string|float $ip
     *
     * @return null|string
     */
    public static function getIp2long($ip)
    {
        $return = null;
        if (is_numeric($ip)) {
            $return = sprintf("%u", floatval($ip));
        }
        if (is_string($ip)) {
            $return = sprintf("%u", floatval(ip2long($ip)));
        }

        return $return;
    }

    /**
     * Returns the wordpress version
     * Note: 2.7.x will return 2.7
     *
     * @return float
     */
    public static function getWordpressVersion()
    {
        static $version = null;
        if (!isset($version)) {
            // Include WordPress version
            require(ABSPATH . WPINC . '/version.php');
            $version = (float) $wp_version;
        }

        return $version;
    }

    /**
     * Determines if the current version of PHP is greater then the supplied value
     *
     * @param $version string
     *                 Defaults to 5.0.0
     *
     * @return bool
     */
    public static function isPHP($version = '5.0.0')
    {
        static $is_php = null;
        $version = (string) $version;
        if (!isset($is_php[$version])) {
            $is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? false : true;
        }

        return $is_php[$version];
    }

    /**
     * Recursively trim whitespace round a string value or of string values within an array
     * Only trims strings to avoid typecasting a variable (to string)
     *
     * @static
     *
     * @param mixed $value Value to trim or array of values to trim.
     *
     * @return mixed Trimmed value or array of trimmed values
     */
    public static function trim_recursive($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        } elseif (is_array($value)) {
            $value = array_map([__CLASS__, 'trim_recursive'], $value);
        }

        return $value;
    }

    public static function writeVarDump($var)
    {
        $output     = var_export($var, true);
        $outputFile = "/tmp/test.txt";
        $fileHandle = fopen($outputFile, "a");
        $boundary   = "--------------- ";
        fwrite($fileHandle, $boundary);
        fwrite($fileHandle, $output);
        fclose($fileHandle);
    }
}
