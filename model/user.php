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
}