<?php
if ( ! function_exists('starts_with'))
{
    /**
     * Determine if a string starts with a given needle.
     *
     * @param  string  $haystack
     * @param  string|array  $needle
     * @return bool
     */
    function avh_starts_with($haystack, $needle)
    {
        return Avh\Support\Str::startsWith($haystack, $needle);
    }
}
