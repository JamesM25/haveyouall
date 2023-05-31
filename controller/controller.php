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
        $this->_f3->set("posts", Database::getRecentPosts());
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
                $this->_f3->set("SESSION.user", Database::getUserFromEmail($email));

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

                Database::createUser($email, $name, $password);

                // Log the user in
                $this->_f3->set("SESSION.user", Database::getUserFromEmail($email));

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

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = $this->readFormInput("title", "Validation::validPostTitle");
            $body = $this->readFormInput("body", "Validation::validPostBody");

            if (empty($this->_f3->get("errors"))) {
                // Construct the post object
                $post = new Post(User::current(), $title, $body, Time::getCurrent());

                // Add the post to the database
                $postId = Database::createPost($post);

                // Reroute to view the post
                $this->_f3->reroute("/post/$postId");
            }
        }

        $this->_f3->set("userTitle", $title);
        $this->_f3->set("userBody", $body);

        $this->render("view/post_form.html");
    }

    /**
     * Renders a post according to the given ID.
     * @param $id int A post ID
     * @return void
     */
    function post($id)
    {
        $post = Database::getPost($id);
        $this->_f3->set("post", $post);
        $this->render("view/post.html");
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
        $this->render("view/admin.html");
    }
}