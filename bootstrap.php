<?php
/**
 * Plugin Name: AVH Framework
 * Plugin URI: http://blog.avirtualhome.com/wordpress-plugins
 * Description: This plugin was written to support all other AVH plugins
 * Version: 1.1.0-dev.1
 * Author: Peter van der Does
 * Author URI: http://blog.avirtualhome.com/
 *
 * Copyright 2013 Peter van der Does (email : peter@avirtualhome.com)
 */

/**
 * |--------------------------------------------------------------------------
 * | Register The Composer Auto Loader
 * |--------------------------------------------------------------------------
 * |
 * | Composer provides a convenient, automatically generated class loader
 * | for our application. We just need to utilize it! We'll require it
 * | into the script here so that we do not have to worry about the
 * | loading of any our classes "manually". Feels great to relax.
 * |
 */
require __DIR__ . '/vendor/autoload.php';
