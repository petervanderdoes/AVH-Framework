<?php
namespace Avh\Framework\Network;

/**
 * Class Visitor
 *
 * @package Avh\Framework\Network
 */
final class Visitor
{
    /**
     * Get the user's IP
     *
     * @return string
     */
    public static function getUserIp()
    {
        $ip_addresses = [];
        foreach ([
                     'HTTP_CF_CONNECTING_IP',
                     'HTTP_CLIENT_IP',
                     'HTTP_X_FORWARDED_FOR',
                     'HTTP_X_FORWARDED',
                     'HTTP_X_CLUSTER_CLIENT_IP',
                     'HTTP_FORWARDED_FOR',
                     'HTTP_FORWARDED',
                     'REMOTE_ADDR'
                 ] as $originating_IP_address) {
            if (array_key_exists($originating_IP_address, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$originating_IP_address]) as $visitors_ip) {
                    $ip_addresses[] = str_replace(' ', '', $visitors_ip);
                }
            }
        }
        // If for some strange reason we don't get an IP we return immediately with 0.0.0.0
        if (empty($ip_addresses)) {
            return '0.0.0.0';
        }
        $ip_addresses = array_values(array_unique($ip_addresses));
        $return = null;
        // In PHP 5.3 and up the function filter_var can be used, much quicker as the regular expression check
        foreach ($ip_addresses as $ip_address) {
            if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
                $return = $ip_address;
                break;
            }
        }

        if (null === $return) {
            $return = '0.0.0.0';
        }

        return $return;
    }
}
