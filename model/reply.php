<?php

/**
 * Represents a reply to a post
 */
class Reply
{
    private $_user;
    private $_text;
    private $_date;

    /**
     * @param $user User|Admin
     * @param $text string
     * @param $date string
     */
    function __construct($user, $text, $date = "")
    {
        $this->_user = $user;
        $this->_text = $text;
        $this->_date = $date;
    }

    /**
     * @return Admin|User The user who created the reply
     */
    function getUser()
    {
        return $this->_user;
    }

    /**
     * @return string The text content of the reply
     */
    function getText()
    {
        return $this->_text;
    }

    /**
     * @return string The reply's creation date
     */
    function getDate()
    {
        return Time::formatTime($this->_date);
    }
}