<?php

/**
 * Represents a regular user account
 */
class User
{
    private $_id;
    private $_name;

    /**
     * Constructs a new User object
     * @param $id int The user ID
     * @param $name string The user name
     */
    function __construct($id, $name)
    {
        $this->_id = $id;
        $this->_name = $name;
    }

    /**
     * @return int The user ID
     */
    function getId()
    {
        return $this->_id;
    }

    /**
     * @return string The username
     */
    function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the current User object, or null if the user is not logged in.
     * @return User|Admin|null
     */
    static function current()
    {
        return $GLOBALS['f3']->get("SESSION.user");
    }
}