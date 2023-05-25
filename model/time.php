<?php
class Time
{
    static function getCurrent()
    {
        // https://stackoverflow.com/a/2215359
        return date('Y-m-d H:i:s');
    }
}
