<?php

/**
 * Contains data for a single post
 */
class Post
{
    private $_user;
    private $_title;
    private $_body;
    private $_time;
    private $_id;
    private $_replyCount;
    private $_views;

    /**
     * Constructs a new Post object
     * @param $user User The user who created the post
     * @param $title string The post title
     * @param $body string The body text
     * @param $time string The post's creation time
     * @param $id int The post's database ID
     * @param $replyCount int The number of replies
     * @param $views int How many times this post has been viewed by a user
     */
    function __construct($user, $title, $body, $time = "", $id = 0, $replyCount = 0, $views = 0)
    {
        $this->_user = $user;
        $this->_title = $title;
        $this->_body = $body;
        $this->_time = $time;
        $this->_id = $id;
        $this->_replyCount = $replyCount;
        $this->_views = $views;
    }

    /**
     * @return User The user who created the post
     */
    function getUser()
    {
        return $this->_user;
    }

    /**
     * @return string The post title
     */
    function getTitle()
    {
        return $this->_title;
    }

    /**
     * @return string The body text
     */
    function getBody()
    {
        return $this->_body;
    }

    /**
     * @return string The time the post was created
     */
    function getTime()
    {
        return $this->_time;
    }

    /**
     * @return int The post's ID in the database. 0 if this has not been initialized yet.
     */
    function getId()
    {
        return $this->_id;
    }

    /**
     * @return int The number of replies
     */
    function getReplyCount()
    {
        return $this->_replyCount;
    }

    function getViews()
    {
        return $this->_views;
    }
}