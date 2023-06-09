<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/../db_haveyouall.php';

/**
 * This class manages all interactions with the database.
 */
class DataLayer
{
    private $_dbh = null;

    const SEARCH_PAGE_LENGTH = 5;

    function __construct()
    {
        try {
            $this->_dbh = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        }
        catch(PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Checks whether the given email address is assigned to an account
     * @param $email
     * @return bool True if an account with the given email address exists
     */
    function emailUsed($email)
    {
        $sql = "SELECT * FROM `Users` WHERE Email=:email";

        $stmt = $this->_dbh->prepare($sql);
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
    function createUser($email, $name, $password)
    {
        $sql = "INSERT INTO `Users` (`Email`, `Name`, `Password`) VALUES (:email, :username, :password)";

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            // password_hash should return a string if it was successful.
            return;
        }

        $stmt = $this->_dbh->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":username", $name, PDO::PARAM_STR);
        $stmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Given a user ID, this method returns a user object if the ID is valid. Otherwise, it returns null.
     * @param $id int A user ID
     * @return Admin|User|null
     */
    function getUser($id)
    {
        $sql = "SELECT * FROM `Users` WHERE ID=:id";

        $stmt = $this->_dbh->prepare($sql);
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
    function checkCredentials($email, $password)
    {
        $sql = "SELECT ID, Password FROM Users WHERE Email=:email";

        $stmt = $this->_dbh->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $result = $stmt->fetchAll();

        // Invalid email
        if (count($result) <= 0) {
            return false;
        }

        return password_verify($password, $result[0]["Password"]);
    }

    /**
     * Given an email address, this method returns a corresponding user object.
     * If the email is not assigned to any users, the method returns false.
     * @param $email string
     * @return Admin|false|User
     */
    function getUserFromEmail($email)
    {
        $sql = "SELECT * FROM `Users` WHERE Email=:email";

        $stmt = $this->_dbh->prepare($sql);
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
    function createPost($post)
    {
        $sql = "INSERT INTO Posts (User, Title, Body, Categories) VALUES (:user, :title, :body, :categories)";

        $stmt = $this->_dbh->prepare($sql);

        $stmt->bindParam(":user", $post->getUser()->getId(), PDO::PARAM_INT);
        $stmt->bindParam(":title", $post->getTitle());
        $stmt->bindParam(":body", $post->getBody());
        $stmt->bindParam(":categories", $post->getCategories());

        $stmt->execute();

        return $this->_dbh->lastInsertId();
    }

    /**
     * Adds a reply to the database
     * @param $postId int ID of the post being replied to
     * @param $reply Reply The reply
     * @return void
     */
    function createReply($postId, $reply)
    {
        $sql = "INSERT INTO Replies (Thread, User, Body) VALUES (:thread, :user, :body)";

        $stmt = $this->_dbh->prepare($sql);

        $stmt->bindValue(":thread", $postId, PDO::PARAM_INT);
        $stmt->bindValue(":user", $reply->getUser()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":body", $reply->getText());

        $stmt->execute();
    }

    /**
     * Inserts a new report into the database
     * @param $report Report
     * @return void
     */
    function createReport($report)
    {
        $sql = "INSERT INTO Reports (Post, User, Body) VALUES (:post, :user, :body)";

        $stmt = $this->_dbh->prepare($sql);

        $stmt->bindValue(":post", $report->getPost()->getId());
        $stmt->bindValue(":user", $report->getUser()->getId());
        $stmt->bindValue(":body", $report->getText());

        $stmt->execute();
    }

    /**
     * Retrieves all user reports from the database
     * @return array Array of user reports
     */
    function getReports()
    {
        $sql = "SELECT * FROM Reports ORDER BY Date DESC";

        $stmt = $this->_dbh->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll();

        $reports = array();
        foreach ($result as $row) {
            $reports[] = $this->reportFromRow($row);
        }

        return $reports;
    }

    /**
     * Given a post ID, this method returns the corresponding Post object, or null if the ID is invalid.
     * @param $id int A post ID
     * @return Post|null
     */
    function getPost($id)
    {
        $sql = "SELECT Posts.*, COUNT(Replies.ID) AS Replies, COUNT(Votes.User) AS Votes 
            FROM Posts
            LEFT JOIN Replies ON Replies.Thread=Posts.ID
            LEFT JOIN Votes ON Votes.Post=Posts.ID
            WHERE Posts.ID=:id
            GROUP BY Posts.ID
            LIMIT 1";
        $stmt = $this->_dbh->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return count($result) > 0 ? self::postFromRow($result[0]) : null;
    }

    /**
     * Removes a post.
     * @param $id int ID of the post being removed
     * @return void
     */
    function removePost($id)
    {
        $sql = "DELETE FROM Posts WHERE ID=:id";
        $stmt = $this->_dbh->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * This method returns an array of the most recent posts, containing no more than 50 elements.
     * @param string $category
     * @param string $filterBy
     * @return array
     */
    function getRecentPosts($category = "", $filterBy = "")
    {
        // ORDER BY cannot use parameters https://stackoverflow.com/a/2543144
        $orderBy = FILTER_TYPES[$filterBy] ?? "Posts.Date";

        $sql = "SELECT Posts.*, COUNT(Replies.ID) AS Replies, COUNT(Votes.User) AS Votes
            FROM Posts
            LEFT JOIN Replies ON Replies.Thread=Posts.ID
            LEFT JOIN Votes ON Votes.Post=Posts.ID
            WHERE Categories LIKE :categories
            GROUP BY Posts.ID
            ORDER BY $orderBy DESC
            LIMIT 50";

        $stmt = $this->_dbh->prepare($sql);

        $stmt->bindValue(":categories", "%$category%");

        $stmt->execute();
        $result = $stmt->fetchAll();

        $posts = array();
        foreach ($result as $row) {
            $posts[$row['ID']] = $this->postFromRow($row);
        }

        return $posts;
    }

    /**
     * Retrieves all replies on a post with the given ID
     * @param $postId int ID of a post
     * @return array Array of replies
     */
    function getReplies($postId)
    {
        $sql = "SELECT Replies.ID, Replies.Thread, Replies.User, Replies.Body, Replies.Date
            FROM Replies
            JOIN Posts ON Replies.Thread=Posts.ID
            WHERE Posts.ID=:id
            ORDER BY Replies.Date DESC";

        $stmt = $this->_dbh->prepare($sql);
        $stmt->bindValue(":id", $postId);
        $stmt->execute();
        $result = $stmt->fetchAll();

        $replies = array();
        foreach ($result as $row) {
            $replies[$row['ID']] = $this->replyFromRow($row);
        }

        return $replies;
    }

    /**
     * Retrieves the current website stats, including the number of topics, total posts (topics + replies), members,
     * and the name of the newest member
     * @return Stats
     */
    function getStats()
    {
        $sql = "SELECT
            (SELECT COUNT(*) FROM Posts) AS topics,
            (SELECT COUNT(*) FROM Posts)+(SELECT COUNT(*) FROM Replies) AS posts,
            (SELECT COUNT(*) FROM Users) AS members,
            (SELECT ID FROM Users ORDER BY ID DESC LIMIT 1) AS newestUser";

        $stmt = $this->_dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();

        return new Stats($result['topics'], $result['posts'], $result['members'], $this->getUser($result['newestUser']));
    }

    /**
     * Searches for posts according to the given search query
     * @param $query string A textual search query
     * @param $page int A page number. Pages are used so that results are not all displayed at once.
     * @return array Posts matching the given query.
     */
    function getSearchResults($query, $page)
    {
        $sql = "SELECT Posts.*, COUNT(Replies.ID) AS Replies, COUNT(Votes.User) AS Votes
            FROM Posts
            LEFT JOIN Replies ON Replies.Thread=Posts.ID
            LEFT JOIN Votes ON Votes.Post=Posts.ID
            WHERE Title LIKE :query OR Posts.Body LIKE :query
            GROUP BY Posts.ID
            ORDER BY Date DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->_dbh->prepare($sql);

        $query = "%$query%";
        $offset = ($page - 1) * self::SEARCH_PAGE_LENGTH;

        $stmt->bindValue(":query", $query);
        $stmt->bindValue(":limit", self::SEARCH_PAGE_LENGTH, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $posts = array();
        foreach ($results as $row) {
            $posts[$row["ID"]] = $this->postFromRow($row);
        }

        return $posts;
    }

    /**
     * Returns the total number of posts that match the given query
     * @param $query string A search query
     * @return int
     */
    function getSearchResultCount($query)
    {
        $sql = "SELECT COUNT(*) FROM Posts
                WHERE Title LIKE :query OR Body LIKE :query";

        $stmt = $this->_dbh->prepare($sql);

        $query = "%$query%";
        $stmt->bindParam(":query", $query);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Finds up to 4 topics with the most recent replies
     * @return array Active topics
     */
    function getActiveTopics()
    {
        $sql = "SELECT Posts.*, COUNT(Replies.ID) AS Replies, COUNT(Votes.User) AS Votes
                    FROM Posts
                    LEFT JOIN Replies ON Replies.Thread = Posts.ID
                    LEFT JOIN Votes ON Votes.Post=Posts.ID
                    GROUP BY Posts.ID
                    ORDER BY MAX(Replies.Date) DESC
                    LIMIT 4";

        $stmt = $this->_dbh->prepare($sql);

        $stmt->execute();

        $posts = array();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $posts[$row["ID"]] = self::postFromRow($row);
        }

        return $posts;
    }

    /**
     * Increments a post's view count by one
     * @param $id int A post ID
     * @return void
     */
    function addView($id)
    {
        $sql = "UPDATE Posts SET Views=Views+1 WHERE ID=:id";
        $stmt = $this->_dbh->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Increments a post's vote count by one
     * @param $post int ID of the post receiving the vote
     * @param $user int ID of the user submitting the vote
     */
    function addVote($post, $user)
    {
        $sql = "INSERT INTO Votes (Post, User) VALUES (:post, :user)";
        $stmt = $this->_dbh->prepare($sql);

        $stmt->bindParam(":post", $post, PDO::PARAM_INT);
        $stmt->bindParam(":user", $user, PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * Removes a vote from a post
     * @param $post int ID of the post that received the vote
     * @param $user int ID of the user who submitted the vote
     */
    function removeVote($post, $user)
    {
        $sql = "DELETE FROM Votes WHERE Post=:post AND User=:user";
        $stmt = $this->_dbh->prepare($sql);

        $stmt->bindParam(":post", $post, PDO::PARAM_INT);
        $stmt->bindParam(":user", $user, PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * Checks whether a post has received a vote from a user with the specified ID
     * @param $post int ID of the post being checked
     * @param $user int ID of the user being checked
     * @return bool True if the post has received a vote from the user with the given ID
     */
    function getVote($post, $user)
    {
        $sql = "SELECT COUNT(*) FROM Votes WHERE Post = :post AND User=:user";

        $stmt = $this->_dbh->prepare($sql);
        $stmt->bindParam(":post", $post, PDO::PARAM_INT);
        $stmt->bindParam(":user", $user, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_NUM);

        return $result[0] != 0;
    }

    private static function userFromRow($row)
    {
        if ($row['Admin']) {
            return new Admin($row['ID'], $row['Name']);
        } else {
            return new User($row['ID'], $row['Name']);
        }
    }
    private function postFromRow($row)
    {
        return new Post(
            $this->getUser($row['User']),
            $row['Title'],
            $row['Body'],
            $row['Categories'],
            $row['Date'],
            $row['ID'],
            $row['Replies'],
            $row['Views'],
            $row['Votes']);
    }
    private function replyFromRow($row)
    {
        return new Reply(
            $this->getUser($row['User']),
            $row['Body'],
            $row['Date']
        );
    }
    private function reportFromRow($row)
    {
        return new Report(
            $row['Body'],
            $this->getPost($row['Post']),
            $this->getUser($row['User']),
            $row['Date']
        );
    }
}