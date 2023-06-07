<?php

/**
 * This class implements methods to validate incoming form data
 */
class Validation
{
    /**
     * Checks whether the email address is valid for the creation of new accounts
     * @param $email string
     * @return bool True if the email is valid and is not assigned to any existing users, otherwise false.
     */
    static function validEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL)
            && !$GLOBALS['data']->emailUsed($email); // Don't allow multiple accounts to be created with the same email
    }

    /**
     * Checks whether a user name is valid
     * @param $name string
     * @return bool True if the name is valid, otherwise false
     */
    static function validName($name)
    {
        return !empty($name);
    }

    /**
     * Checks whether a post title is valid
     * @param $title string
     * @return bool True if the post title is valid, otherwise false
     */
    static function validPostTitle($title)
    {
        return strlen($title) >= 3;
    }

    /**
     * Checks whether a post body is valid
     * @param $body string
     * @return bool True if the post body is valid, otherwise false
     */
    static function validPostBody($body)
    {
        return strlen($body) >= 3;
    }

    /**
     * Checks whether the given categories are valid
     * @param $categories array Array of category strings
     * @return bool True if the categories are valid
     */
    static function validCategories($categories)
    {
        foreach ($categories as $category) {
            if (!in_array($category, Post::CATEGORIES)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks whether the given login credentials are valid
     * @param $email string
     * @param $password string
     * @return bool True if the given email and password may be used to log into an account, otherwise false
     */
    static function validLogin($email, $password)
    {
        return $GLOBALS['data']->checkCredentials($email, $password);
    }

    /**
     * Checks whether the given report text is valid
     * @param $report string
     * @return bool True if the given report text is less than or equal to the maximum length
     */
    static function validReport($report)
    {
        return strlen($report) <= Report::MAX_LENGTH;
    }

    /**
     * @return bool True if the user is logged into an account, otherwise false
     */
    static function isLoggedIn()
    {
        return $GLOBALS['f3']->exists("SESSION.user");
    }

    /**
     * @return bool True if the user is logged in and has administrator permissions, otherwise false
     */
    static function isAdmin()
    {
        return self::isLoggedIn() && get_class(User::current()) === "Admin";
    }

    /**
     * Checks whether the user has voted for the post with the given ID
     * @param $postId int ID of a post
     * @return bool True if the user has already voted for this post
     */
    static function hasVoted($postId)
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        $userId = User::current()->getId();

        return $GLOBALS['data']->getVote($postId, $userId);
    }
}