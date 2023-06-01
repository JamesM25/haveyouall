<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/../db_haveyouall.php';

/**
 * This class manages all interactions with the database.
 */
class Database
{
    private static $_db = null;

    private static function getDatabase()
    {
        if (self::$_db === null) {
            try {
                self::$_db = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
            }
            catch(PDOException $e) {
                echo $e->getMessage();
            }
        }
        return self::$_db;
    }

    /**
     * Checks whether the given email address is assigned to an account
     * @param $email
     * @return bool True if an account with the given email address exists
     */
    static function emailUsed($email)
    {
        $db = self::getDatabase();

        $sql = "SELECT * FROM `Users` WHERE Email=:email";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        return count($stmt->fetchAll()) > 0;
    }

    /**
     * Creates a new user in the database
     * @param $email string The user's email
     * @param $name string The username
     * @param $password string The user's password
     * @return void
     */
    static function createUser($email, $name, $password)
    {
        $db = self::getDatabase();

        $sql = "INSERT INTO `Users` (`Email`, `Name`, `Password`) VALUES (:email, :username, :password)";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":username", $name, PDO::PARAM_STR);
        $stmt->bindParam(":password", $password, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Given a user ID, this method returns a user object if the ID is valid. Otherwise, it returns null.
     * @param $id int A user ID
     * @return Admin|User|null
     */
    static function getUser($id)
    {
        $db = self::getDatabase();

        $sql = "SELECT * FROM `Users` WHERE ID=:id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll();
        if (count($result) == 0) {
            // No account with the provided email
            return null;
        }

        // Put the result into a User object
        return self::userFromRow($result[0]);
    }

    /**
     * Checks whether the given login credentials are valid
     * @param $email string an email address
     * @param $password string a password
     * @return bool True if the credentials are valid
     */
    static function checkCredentials($email, $password)
    {
        $db = self::getDatabase();

        $sql = "SELECT `ID` FROM `Users` WHERE `Email`=:email AND `Password`=:password";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $password, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetchAll();

        return count($result) > 0;
    }

    /**
     * Given an email address, this method returns a corresponding user object.
     * If the email is not assigned to any users, the method returns false.
     * @param $email string
     * @return Admin|false|User
     */
    static function getUserFromEmail($email)
    {
        $db = self::getDatabase();

        $sql = "SELECT * FROM `Users` WHERE Email=:email";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetchAll();
        if (count($result) == 0) {
            // No account with the provided email
            return false;
        }

        return self::userFromRow($result[0]);
    }

    /**
     * Creates a new post and returns it's ID
     * @param $post Post The post contents
     * @return int The ID of the post
     */
    static function createPost($post)
    {
        $db = self::getDatabase();

        $sql = "INSERT INTO `Posts` (`User`, `Title`, `Body`, `Date`) VALUES (:user, :title, :body, :date)";

        $stmt = $db->prepare($sql);

        $stmt->bindParam(":user", $post->getUser()->getId(), PDO::PARAM_INT);
        $stmt->bindParam(":title", $post->getTitle(), PDO::PARAM_STR);
        $stmt->bindParam(":body", $post->getBody(), PDO::PARAM_STR);
        $stmt->bindParam(":date", $post->getTime(), PDO::PARAM_STR);

        $stmt->execute();

        return $db->lastInsertId();
    }

    /**
     * Given a post ID, this method returns the corresponding Post object, or null if the ID is invalid.
     * @param $id int A post ID
     * @return Post|null
     */
    static function getPost($id)
    {
        $db = self::getDatabase();

        // Retrieve the ID of the most recent post
        $sql = "SELECT * FROM `Posts` WHERE `ID`=:id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return count($result) > 0 ? self::postFromRow($result[0]) : null;
    }

    /**
     * This method returns an array of the most recent posts, containing no more than 50 elements.
     * @return array
     */
    static function getRecentPosts()
    {
        $db = self::getDatabase();

        $sql = "SELECT * FROM `Posts` ORDER BY `Date` DESC LIMIT 50";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        $posts = array();
        foreach ($result as $row) {
            $posts[$row['ID']] = self::postFromRow($row);
        }

        return $posts;
    }

    private static function userFromRow($row)
    {
        if ($row['Admin']) {
            return new Admin($row['ID'], $row['Name']);
        } else {
            return new User($row['ID'], $row['Name']);
        }
    }
    private static function postFromRow($row)
    {
        return new Post(
            self::getUser($row['User']),
            $row['Title'],
            $row['Body'],
            $row['Date']);
    }
}