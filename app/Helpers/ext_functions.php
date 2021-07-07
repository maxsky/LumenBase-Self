<?php

if (!extension_loaded('jsond')) {
    /**
     * @param mixed $value
     * @param int   $flags [optional]
     * @param int   $depth [optional]
     *
     * @return string|false a JSON encoded string on success or FALSE on failure.
     */
    function jsond_encode($value, int $flags = 0, int $depth = 512) {
        return json_encode($value, $flags, $depth);
    }

    /**
     * @param string    $json
     * @param bool|null $associative [optional]
     * @param int       $depth       [optional]
     * @param int       $flags       [optional]
     *
     * @return mixed the value encoded in json in appropriate PHP type.
     */
    function jsond_decode(string $json, ?bool $associative = false, int $depth = 512, int $flags = JSON_UNESCAPED_UNICODE) {
        return json_decode($json, $associative, $depth, $flags);
    }

    /**
     * @return string Returns the error message on success, "No error" if no error has occurred.
     */
    function jsond_last_error_msg(): string {
        return json_last_error_msg();
    }

    /**
     * @return int an integer.
     */
    function jsond_last_error(): int {
        return json_last_error();
    }
}
