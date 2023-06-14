<?php

/**
 * Implements methods that handle all routes
 */
class Controller
{
    private $_f3;

    /**
     * Constructs a new Controller object
     * @param $f3 Prefab The fat-free object
     */
    function __construct($f3)
    {
        $this->_f3 = $f3;
    }

    /**
     * Renders the given view page inside of view/base.html
     * @param $path string Path to a view page
     * @return void
     */
    private function render($path)
    {
        $this->_f3->set("content", $path);
        $view = new Template();
        echo $view->render('view/base.html');
    }

    /**
     * Reads input data from $_POST
     * @param $name string The name of an input field
     * @param $validate string A validation function. If left as an empty string, validation will not occur.
     * @param $default mixed The default value, if the input is not present within $_POST
     * @return mixed|string The corresponding value from $_POST, or $default if no value was found
     */
    private function readFormInput($name, $validate = "", $default = "")
    {
        $value = $_POST[$name] ?? $default;

        if ($validate != "" && !$validate($value)) {
            $this->_f3->set("errors['$name']", "Invalid $name");
        }

        return $value;
    }

    /**
     * Renders the home page
     * @return void
     */
    function home()
    {
        $category = $this->_f3->get("SESSION.category") ?? "";
        if (isset($_GET["category"])) {
            $category = $_GET["category"];
            $this->_f3->set("SESSION.category", $category);
        }

        $filter = $this->_f3->get("SESSION.filter") ?? FILTER_TYPES[0];
        if (isset($_GET["filter"])) {
            $filter = $_GET["filter"];
            $this->_f3->set("SESSION.filter", $filter);
        }

        $this->_f3->set("SESSION.category", $category);
        $this->_f3->set("SESSION.filter", $filter);

        $this->_f3->set("posts", $GLOBALS['data']->getRecentPosts($category, $filter));
        $this->_f3->set("stats", $GLOBALS['data']->getStats());
        $this->_f3->set("activeTopics", $GLOBALS['data']->getActiveTopics());
        $this->render("view/home.html");
    }

    /**
     * Renders the login page, and handles incoming form data when the user attempts to log into an account.
     * @return void
     */
    function login()
    {
        $email = "";
        $password = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $this->readFormInput("email");
            $password = $this->readFormInput("password");

            if (Validation::validLogin($email, $password)) {
                // Log the user in
                $this->_f3->set("SESSION.user", $GLOBALS['data']->getUserFromEmail($email));

                // Reroute to home
                $this->_f3->reroute("/");
            } else {
                $this->_f3->set("errors['login']", "Invalid email or password");
            }
        }

        $this->_f3->set("userEmail", $email);
        $this->_f3->set("userPassword", $password);

        $this->render("view/login.html");
    }

    /**
     * Logs the user out, and redirects their browser to the home page
     * @return void
     */
    function logout()
    {
        // Log the user out
        $this->_f3->clear("SESSION.user");

        $this->_f3->reroute('/');
    }

    /**
     * Renders the signup page, and handles incoming form data when the user attempts to create an account.
     * @return void
     */
    function signup()
    {
        $email = "";
        $name = "";
        $password = "";
        $passwordConfirm = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->readFormInput("email", "Validation::validEmail");
            $name = $this->readFormInput("name", "Validation::validName");
            $password = $this->readFormInput("password");
            $passwordConfirm = $this->readFormInput("passwordConfirm");

            if ($passwordConfirm !== $password) {
                $this->_f3->set("errors['passwordMismatch']", "Passwords do not match");
            }

            if (empty($this->_f3->get('errors'))) {

                $GLOBALS['data']->createUser($email, $name, $password);

                // Log the user in
                $this->_f3->set("SESSION.user", $GLOBALS['data']->getUserFromEmail($email));

                $this->render('view/success.html');

                return;
            }
        }

        $this->_f3->set('userEmail', $email);
        $this->_f3->set('userName', $name);
        $this->_f3->set('userPassword', $password);
        $this->_f3->set('userPasswordConfirm', $passwordConfirm);

        $this->render("view/signup.html");
    }

    /**
     * Renders the create a post page, and handles incoming form data when the user attempts to submit a post.
     * If the user is not logged into an account, they will be redirected to the login page.
     * @return void
     */
    function createPost()
    {
        if (!Validation::isLoggedIn()) {
            $this->_f3->reroute('/login');
        }

        $title = "";
        $body = "";
        $categories = array();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = $this->readFormInput("title", "Validation::validPostTitle");
            $body = $this->readFormInput("body", "Validation::validPostBody");
            $categories = $this->readFormInput("categories", "Validation::validCategories", array());

            if (empty($this->_f3->get("errors"))) {
                // Construct the post object
                $post = new Post(User::current(), $title, $body, implode(", ", $categories), Time::getCurrent());

                // Add the post to the database
                $postId = $GLOBALS['data']->createPost($post);

                // Reroute to view the post
                $this->_f3->reroute("/post/$postId");
            }
        }

        $this->_f3->set("userTitle", $title);
        $this->_f3->set("userBody", $body);
        $this->_f3->set("userCategories", $categories);

        $this->render("view/post_form.html");
    }

    function reportPost($id)
    {
        if (!Validation::isLoggedIn()) {
            $this->_f3->reroute('/login');
        }

        $db = $GLOBALS['data'];

        $post = $db->getPost($id);
        $this->_f3->set("post", $post);

        $report = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $report = $this->readFormInput("report", "Validation::validReport");

            if (empty($this->_f3->get("errors"))) {
                $report = new Report($report, $post, User::current());

                // Add the post to the database
                $postId = $GLOBALS['data']->createReport($report);

                // Reroute to view the post
                $this->_f3->reroute("/post/$id");
            }
        }

        $this->_f3->set("userReport", $report);

        $this->render("view/report_form.html");
    }

    /**
     * Renders a post according to the given ID.
     * @param $id int A post ID
     * @return void
     */
    function post($id)
    {
        $post = $GLOBALS['data']->getPost($id);

        $reply = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (!Validation::isLoggedIn()) {
                $this->_f3->reroute('/login');
            }

            $reply = $this->readFormInput("reply");

            $postReply = new Reply(User::current(), $reply);

            $GLOBALS['data']->createReply($id, $postReply);

            // Clear the textarea value
            $reply = "";
        }

        // Use session to prevent views from being added multiple times by the same user.
        if (empty($this->_f3->get("SESSION.viewedPosts")) || !in_array($id, $this->_f3->get("SESSION.viewedPosts"))) {
            $GLOBALS['data']->addView($post->getId());

            $viewedPosts = $this->_f3->get("SESSION.viewedPosts") ?? array();
            $viewedPosts[] = $id;

            $this->_f3->set("SESSION.viewedPosts", $viewedPosts);
        }

        $this->_f3->set("userReply", $reply);

        $this->_f3->set("post", $post);
        $this->_f3->set("replies", $GLOBALS['data']->getReplies($post->getId()));
        $this->_f3->set("postId", $id);
        $this->render("view/post.html");
    }

    /**
     * Adds a vote to the post with the given ID
     * @param $id int A post ID
     */
    function vote($id)
    {
        if (!Validation::isLoggedIn()) {
            $this->_f3->reroute("/login");
        }

        $userId = User::current()->getId();

        if (!Validation::hasVoted($id)) {
            $GLOBALS['data']->addVote($id, $userId);
        } else {
            $GLOBALS['data']->removeVote($id, $userId);
        }

        $this->_f3->reroute("/post/$id");
    }

    /**
     * Displays the results of a search query using GET
     * @return void
     */
    function search()
    {
        if (!isset($_GET["query"])) {
            $this->_f3->reroute("/");
        }

        $query = $_GET["query"];
        $page = $_GET["page"] ?? 1;

        $searchResultTotal = $GLOBALS['data']->getSearchResultCount($query);
        $pageCount = max(ceil($searchResultTotal / DataLayer::SEARCH_PAGE_LENGTH), 1);

        // Clamp current page to [1, $pageCount]
        $page = min(max(1, $page), $pageCount);

        $searchResults = $GLOBALS['data']->getSearchResults($query, $page);

        $this->_f3->set("userSearch", $query);
        $this->_f3->set("userPage", $page);
        $this->_f3->set("searchPageCount", $pageCount);
        $this->_f3->set("searchResults", $searchResults);
        $this->_f3->set("searchResultTotal", $searchResultTotal);

        $this->render("view/search.html");
    }

    /**
     * Removes a post
     * If the user does not have administrator permissions, a 404 error will be displayed.
     * @param $id int A post ID
     * @return void
     */
    function removePost($id)
    {
        if (!Validation::isAdmin()) {
            $this->_f3->error(404);
        }

        User::current()->removePost($id);

        // Reroute to home
        $this->_f3->reroute('/');
    }

    /**
     * Renders the administrator dashboard page.
     * If the user does not have administrator permissions, a 404 error will be displayed.
     * @return void
     */
    function admin()
    {
        if (!Validation::isAdmin()) {
            $this->_f3->error(404);
        }

        $this->_f3->set("reports", $GLOBALS['data']->getReports());
        $this->render("view/admin.html");
    }
}