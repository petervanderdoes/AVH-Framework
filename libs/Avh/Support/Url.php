<?php
namespace Avh\Support;


/**
 *
 * @author pdoes
 *
 */
class Url
{

    /**
     * Determine if the given path is a valid URL.
     *
     * @param string $path
     * @return bool
     */
    static public function isValidUrl($path)
    {
        if ( avh_starts_with($path, array('#', '//', 'mailto:', 'tel:' ))) {
            return true;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }
}