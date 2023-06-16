<?php

/**
 * Represents a user account with administrator permissions
 */
class Admin extends User
{
    /**
     * Removes a post
     * @param $postId int
     * @return void
     */
    function removePost($postId)
    {
        $GLOBALS['data']->removePost($postId);
    }
}