<?php

/**
 * Implements methods for processing time information
 */
class Time
{
    /**
     * @return string The current time, in a format compatible with MySQL's DATETIME data type.
     */
    static function getCurrent()
    {
        // https://stackoverflow.com/a/2215359
        return date('Y-m-d H:i:s');
    }
}
