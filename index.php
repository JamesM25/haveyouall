<?php
/*
 * James Motherwell
 * 5/3/2023
 * 328/haveyouall/index.php
 * Controller for SDEV328 final project
 */

// Turn on error reporting
ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once "vendor/autoload.php";

require_once 'model/db.php';

// Create an F3 (Fat-Free Framework) object
$f3 = Base::instance();

// TODO: The current icon is a placeholder. We should find something better eventually.
const ICON_PATH = 'images/mbox-icon.png';

// Default route
$f3->route('GET /', function ($f3) {
    // Set the icon path
    $f3->set('favicon', ICON_PATH);

    $view = new Template();
    echo $view->render('view/home.html');
});

$f3->route('GET /login', function ($f3) {
    // Set the icon path
    $f3->set('favicon', ICON_PATH);

    $view = new Template();
    echo $view->render('view/login.html');
});

$f3->route('GET /signup', function ($f3) {
    // Set the icon path
    $f3->set('favicon', ICON_PATH);

    $view = new Template();
    echo $view->render('view/signup.html');
});

// Simple route for testing database access
$f3->route('GET /db-test', function() {
    $db = getDatabase();

    $stmt = $db->prepare("SELECT * FROM Users");
    $stmt->execute();
    $result = $stmt->get_result();

    var_dump($result->fetch_row());
});

// Run Fat-Free
$f3->run();