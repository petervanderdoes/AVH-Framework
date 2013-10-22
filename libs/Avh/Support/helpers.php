<?php
if (!function_exists('avh_starts_with')) {

    /**
     * Determine if a string starts with a given needle.
     *
     * @param  string       $haystack
     * @param  string|array $needle
     * @return bool
     */
    function avh_starts_with($haystack, $needle)
    {
        return Avh\Support\Str::startsWith($haystack, $needle);
    }
}

if (!function_exists('avh_is_valid_url')) {
    /**
     * Determine if the given path is a valid URL.
     *
     * @param  string $path
     * @return bool
     */
    function avh_is_valid_url($path)
    {
        if (avh_starts_with($path, array('#', '//', 'mailto:', 'tel:'))) {
            return true;
        }
    
        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }
}