<?php
namespace Avh\Utility;

/**
 * Semi-Autoloader.
 *
 * As we can't control the environment where the AVH Software will run we
 * can not rely on the fact that the Standard PHP Library (SPL) is enabled
 * on the server. There for we can't create a true autoloader class.
 * This class is PSR-0 compliant vendor-prefix or "namespace" loading.
 *
 * @link PSR-0
 *       https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 */
class Loader
{

    private static $objects;

    private static $dir;

    private static $class_file_prefix;

    private static $class_name_prefix;

    private static $class_name;

    private static $class_file;

    const NAMESPACE_SEPERATOR = '\\';

    const PREFIX_SPERATOR = '_';

    /**
     * Get an instance of a class.
     *
     * @param string $class
     *            The class name
     * @param  boolean $store
     * @return object
     */
    public static function getInstance($class, $store = true)
    {
        self::setClassLoaderProperties($class);
        if (isset(self::$objects[self::$class_name])) {
            return (self::$objects[self::$class_name]);
        }
        if (!class_exists(self::$class_name)) {
            require_once (self::$dir . self::$class_file);
        }
        $_object = new self::$class_name();
        if ($store) {
            self::$objects[self::$class_name] = $_object;
        }

        return $_object;
    }

    /**
     * Load a class file but don't start the instance.
     * We use this loading helper classes for example.
     *
     * @param string $class
     * @param string $type
     */
    public static function loadClass($class)
    {
        self::setClassLoaderProperties($class);

        if (!class_exists(self::$class_name)) {
            require_once (self::$dir . self::$class_file);
        }
    }

    /**
     * Sets the internal properties needed
     *
     * It sets the class name (self::$class_name) and where to load the
     * file from (self::$class_file)
     *
     * @param string $class
     *            Name of the class you want to load
     */
    private static function setClassLoaderProperties($class)
    {
        $_file = str_replace(self::NAMESPACE_SEPERATOR, DIRECTORY_SEPARATOR, $class) . '.php';
        list ($type) = explode(self::NAMESPACE_SEPERATOR, $class, 2);
        $_class = end(explode(self::NAMESPACE_SEPERATOR, $class));
        $_name = ('Avh' == $type) ? 'AVH2_' . $_class : self::$class_name_prefix . $_class;
        self::$class_name = $_name;
        self::$class_file = $_file;
    }

    /**
     *
     * @param string $dir
     *            Directory to set
     */
    public static function setDir($dir)
    {
        self::$dir = trailingslashit($dir);
    }

    /**
     *
     * @param string $class_name_prefix
     *            the class name prefix to set
     */
    public static function setClassNamePrefix($class_name_prefix)
    {
        self::$class_name_prefix = $class_name_prefix;
    }

    /**
     *
     * @param array $properties
     */
    public static function setClassProperties($properties)
    {
        $default_properties = array('type' => 'system', 'store' => false);
    }
}
