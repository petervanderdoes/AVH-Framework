<?php
namespace Avh\Network;

use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;


class Session extends \Symfony\Component\HttpFoundation\Session\Session
{

    /**
     *
     * List of options for $options array with their defaults.
     *
     * @see http://php.net/session.configuration for options
     *      but we omit 'session.' from the beginning of the keys for convenience.
     *
     *      ("auto_start", is not supported as it tells PHP to start a session before
     *      PHP starts to execute user-land code. Setting during runtime has no effect).
     *
     *      cache_limiter, "nocache" (use "0" to prevent headers from being sent entirely).
     *      cookie_domain, ""
     *      cookie_httponly, ""
     *      cookie_lifetime, "0"
     *      cookie_path, "/"
     *      cookie_secure, ""
     *      entropy_file, ""
     *      entropy_length, "0"
     *      gc_divisor, "100"
     *      gc_maxlifetime, "1440"
     *      gc_probability, "1"
     *      hash_bits_per_character, "4"
     *      hash_function, "0"
     *      name, "avh_COOKIEHASH" COOKIEHASH is defined by WordPress
     *      referer_check, ""
     *      serialize_handler, "php"
     *      use_cookies, "1"
     *      use_only_cookies, "1"
     *      use_trans_sid, "0"
     *      upload_progress.enabled, "1"
     *      upload_progress.cleanup, "1"
     *      upload_progress.prefix, "upload_progress_"
     *      upload_progress.name, "PHP_SESSION_UPLOAD_PROGRESS"
     *      upload_progress.freq, "1%"
     *      upload_progress.min-freq, "1"
     *      url_rewriter.tags, "a=href,area=href,frame=src,form=,fieldset="
     *
     * @param array $options
     *            Session configuration options.
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $default_options = array('name' => 'avh_' . COOKIEHASH);
        $options = array_merge($default_options, $options);
        parent::__construct(new NativeSessionStorage($options), new NamespacedAttributeBag('avh_framework'));
    }
}
