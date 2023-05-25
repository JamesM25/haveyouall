<?php
class Controller
{
    private $_f3;

    function __construct($f3)
    {
        $this->_f3 = $f3;
    }

    private function render($path)
    {
        $this->_f3->set("content", $path);
        $view = new Template();
        echo $view->render('view/base.html');
    }

    private function readFormInput($name, $validate = "", $default = "")
    {
        $value = $_POST[$name] ?? $default;

        if ($validate != "" && !$validate($value)) {
            $this->_f3->set("errors['$name']", "Invalid $name");
        }

        return $value;
    }

    function home()
    {
        $this->_f3->set("posts", Database::getRecentPosts());
        $this->render("view/home.html");
    }

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

    function logout()
    {
        // Log the user out
        $this->_f3->clear("SESSION.user");

        $this->_f3->reroute('/');
    }

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

    function post($id)
    {
        $post = Database::getPost($id);
        $this->_f3->set("post", $post);
        $this->render("view/post.html");
    }

    function admin()
    {
        if (!Validation::isAdmin()) {
            $this->_f3->error(404);
        }
        $this->render("view/admin.html");
    }
}