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
                $this->_f3->set("SESSION.user", Database::getUserIdFromEmail($email));

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
            $name = $this->readFormInput("name");
            $password = $this->readFormInput("password");
            $passwordConfirm = $this->readFormInput("passwordConfirm");

            if ($passwordConfirm !== $password) {
                $this->_f3->set("errors['passwordMismatch']", "Passwords do not match");
            }

            if (empty($this->_f3->get('errors'))) {

                Database::createUser($email, $name, $password);

                // Log the user in
                $this->_f3->set("SESSION.user", Database::getUserIdFromEmail($email));

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
        }

        $this->_f3->set("userTitle", $title);
        $this->_f3->set("userBody", $body);

        $this->render("view/post_form.html");
    }
}