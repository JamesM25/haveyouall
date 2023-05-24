<?php

/*
 * Path to the database connection file.
 * Since we're hosting this on our own domains, this will be different for each person.
 * This was the best solution we could come up with without requiring each person to configure the file differently.
 * Originally we attempted to use /home/../db_haveyouall.php, but the ../ did not work.
 */
const DB_PATH = '../../../db_haveyouall.php';

class Database
{
    private static $_db = null;

    static function getDatabase()
    {
        if (self::$_db === null) {
            require_once DB_PATH;
            self::$_db = $cnxn;
        }
        return self::$_db;
    }

    static function emailUsed($email)
    {
        $db = self::getDatabase();

        $sql = "SELECT * FROM `Users` WHERE Email=?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    static function createUser($email, $name, $password)
    {
        $db = self::getDatabase();

        $sql = "INSERT INTO `Users` (`Email`, `Name`, `Password`) VALUES (?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("sss", $email, $name, $password);
        $stmt->execute();
    }

    static function getUser($id)
    {
        $db = self::getDatabase();

        $sql = "SELECT * FROM `Users` WHERE ID=?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row == null) {
            // No account with the provided email
            return null;
        }

        // Put the result into a User object
        return new User($row['Name']);
    }

    static function checkCredentials($email, $password)
    {
        $db = self::getDatabase();

        $sql = "SELECT `ID` FROM `Users` WHERE `Email`=? AND `Password`=?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    static function getUserIdFromEmail($email)
    {
        $db = self::getDatabase();

        $sql = "SELECT `ID` FROM `Users` WHERE Email=?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_array();
        if ($row == null) {
            // No account with the provided email
            return false;
        }

        return $row[0];
    }
}