<?php
namespace Avh\Network;

final class Visitor
{

    /**
     * Get the user's IP
     *
     * @return string
     */
    public static function getUserIp()
    {
        $ip = array();
        foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $_key) {
            if (array_key_exists($_key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$_key]) as $_visitors_ip) {
                    $ip[] = str_replace(' ', '', $_visitors_ip);
                }
            }
        }
        // If for some strange reason we don't get an IP we return imemdiately with 0.0.0.0
        if (empty($ip)) {
            return '0.0.0.0';
        }
        $ip = array_values(array_unique($ip));
        $return = null;
        // In PHP 5.3 and up the function filter_var can be used, much quicker as the regular expression check
        foreach ($ip as $_i) {
            if (filter_var($_i, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
                $return = $_i;
                break;
            }
        }

        if (null === $return) {
            $return = '0.0.0.0';
        }

        return $return;
    }
}
