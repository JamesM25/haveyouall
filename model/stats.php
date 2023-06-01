<?php

/**
 * Contains statistical information about the site
 */
class Stats
{
    private $_topics;
    private $_posts;
    private $_members;
    private $_newestUser;

    /**
     * @param $topics int
     * @param $posts int
     * @param $members int
     * @param $newestUser User|Admin|null
     */
    function __construct($topics, $posts, $members, $newestUser)
    {
        $this->_topics = $topics;
        $this->_posts = $posts;
        $this->_members = $members;
        $this->_newestUser = $newestUser;
    }

    /**
     * @return int Number of threads
     */
    function getTopics()
    {
        return $this->_topics;
    }

    /**
     * @return int Number of posts, including both threads and replies
     */
    function getPosts()
    {
        return $this->_posts;
    }

    /**
     * @return int The number of user accounts
     */
    function getMembers()
    {
        return $this->_members;
    }

    /**
     * @return Admin|User|null The newest user
     */
    function getNewestUser()
    {
        return $this->_newestUser;
    }

    /**
     * @return string The username of the newest member, or "..." if no members exist yet.
     */
    function getNewestUserName()
    {
        return $this->_newestUser != null
            ? $this->_newestUser->getName()
            : "...";
    }
}