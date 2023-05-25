<?php
class User
{
    private $_name;

    function __construct($name)
    {
        $this->_name = $name;
    }

    function getName()
    {
        return $this->_name;
    }

    static function current()
    {
        return Database::getUser($GLOBALS['f3']->get("SESSION.user"));
    }
}