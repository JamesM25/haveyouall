<?php
class User
{
    private $_id;
    private $_name;

    function __construct($id, $name)
    {
        $this->_id = $id;
        $this->_name = $name;
    }

    function getId()
    {
        return $this->_id;
    }
    function getName()
    {
        return $this->_name;
    }

    static function current()
    {
        return $GLOBALS['f3']->get("SESSION.user");
    }
}