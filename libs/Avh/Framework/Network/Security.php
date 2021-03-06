<?php
namespace Avh\Framework\Network;

/**
 * Class Security
 *
 * @package Avh\Framework\Network
 */
final class Security
{
    /**
     * Local nonce creation.
     * WordPress uses the UID and sometimes I don't want that
     * Creates a random, one time use token.
     *
     * @param string|int $action Scalar value to add context to the nonce.
     *
     * @return string The one use form token
     */
    public static function createNonce($action = - 1)
    {
        $tick = wp_nonce_tick();

        return substr(wp_hash($tick . $action, 'nonce'), - 12, 10);
    }

    /**
     * Local nonce verification.
     * WordPress uses the UID and sometimes I don't want that
     * Verify that correct nonce was used with time limit.
     * The user is given an amount of time to use the token, so therefore, since the
     * $action remain the same, the independent variable is the time.
     *
     * @param string     $nonce  Nonce that was used in the form to verify
     * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
     *
     * @return integer|false Whether the nonce check passed or failed.
     */
    public static function verifyNonce($nonce, $action = - 1)
    {
        $return = false;
        $tick   = wp_nonce_tick();
        // Nonce generated 0-12 hours ago
        if (substr(wp_hash($tick . $action, 'nonce'), - 12, 10) == $nonce) {
            $return = 1;
        } elseif (substr(wp_hash(($tick - 1) . $action, 'nonce'), - 12, 10) ==
                  $nonce
        ) { // Nonce generated 12-24 hours ago
            $return = 2;
        }

        return $return;
    }
}
