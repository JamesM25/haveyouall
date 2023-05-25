<?php
class Validation
{
    static function validEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL)
            && !Database::emailUsed($email); // Don't allow multiple accounts to be created with the same email
    }

    static function validName($name)
    {
        return !empty($name);
    }

    static function validPostTitle($title)
    {
        return strlen($title) >= 3;
    }
    static function validPostBody($body)
    {
        return strlen($body) >= 3;
    }

    static function validLogin($email, $password)
    {
        return Database::checkCredentials($email, $password);
    }

    static function isLoggedIn()
    {
        return $GLOBALS['f3']->exists("SESSION.user");
    }

    static function isAdmin()
    {
        return self::isLoggedIn() && get_class(User::current()) === "Admin";
    }
}