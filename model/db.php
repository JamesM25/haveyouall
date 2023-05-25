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
                // echo 'Connected to database!';
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

        $row = $result[0];

        // Put the result into a User object
        return new User($row['Name']);
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

    static function getUserIdFromEmail($email)
    {
        $db = self::getDatabase();

        $sql = "SELECT `ID` FROM `Users` WHERE Email=:email";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetchAll();
        if (count($result) == 0) {
            // No account with the provided email
            return false;
        }

        return $result[0][0];
    }
}