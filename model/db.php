<?php

/*
 * Path to the database connection file.
 * Since we're hosting this on our own domains, this will be different for each person.
 * This was the best solution we could come up with without requiring each person to configure the file differently.
 * Originally we attempted to use /home/../db_haveyouall.php, but the ../ did not work.
 */
const DB_PATH = '../../../db_haveyouall.php';
require_once DB_PATH;

class Database
{
    private static $_db = null;

    static function getDatabase()
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

    static function emailUsed($email)
    {
        $db = self::getDatabase();

        $sql = "SELECT * FROM `Users` WHERE Email=:email";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        return count($stmt->fetchAll()) > 0;
    }

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

        // Retrieve the ID of the most recent post
        $sql = "SELECT `ID` FROM `Posts` ORDER BY `ID` DESC LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        // Return the ID of the most recent post
        return $result[0][0];
    }

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

    private static function userFromRow($row)
    {
        return new User($row['ID'], $row['Name']);
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