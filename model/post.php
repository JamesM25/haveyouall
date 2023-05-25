<?php
class Post
{
    private $_user;
    private $_title;
    private $_body;
    private $_time;

    function __construct($user, $title, $body, $time)
    {
        $this->_user = $user;
        $this->_title = $title;
        $this->_body = $body;
        $this->_time = $time;
    }

    function getUser()
    {
        return $this->_user;
    }

    function getTitle()
    {
        return $this->_title;
    }

    function getBody()
    {
        return $this->_body;
    }

    function getTime()
    {
        return $this->_time;
    }
}