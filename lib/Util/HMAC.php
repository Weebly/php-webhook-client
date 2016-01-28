<?php

namespace Util;

/**
 * Class HMAC
 * @package Util
 *
 * Utility class for HMAC functions in this context
 */
class HMAC {

    /**
     * Check if payload hashed with secret matches the expected hash
     *
     * @param $payload
     * @param $secret
     * @param $current_hash
     * @return bool
     */
    public static function isHmacValid($payload, $secret, $current_hash) {
        $verification_hash = hash_hmac('sha256', $payload, $secret, false);
        return ($verification_hash == $current_hash);
    }
}