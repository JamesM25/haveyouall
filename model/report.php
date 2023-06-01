<?php

/**
 * Represents a user's report of a post that should be taken down.
 */
class Report
{
    const MAX_LENGTH = 1000;

    private $_text;
    private $_post;
    private $_user;
    private $_date;

    /**
     * @param $text string
     * @param $post Post
     * @param $user User|Admin
     * @param $date string
     */
    function __construct($text, $post, $user, $date = "")
    {
        $this->_text = $text;
        $this->_post = $post;
        $this->_user = $user;
        $this->_date = $date;
    }

    /**
     * @return Post The post that is the subject of this report
     */
    function getPost()
    {
        return $this->_post;
    }

    /**
     * @return User|Admin The user who created the report
     */
    function getUser()
    {
        return $this->_user;
    }

    /**
     * @return string The text contents of the report
     */
    function getText()
    {
        return $this->_text;
    }

    /**
     * @return string The report's creation date
     */
    function getDate()
    {
        return $this->_date;
    }
}