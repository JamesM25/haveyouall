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

require 'model/db.php';

// Create an F3 (Fat-Free Framework) object
$f3 = Base::instance();

$f3->route('GET /', function () {
    $view = new Template();
    echo $view->render('view/home.html');
});

$f3->route('GET /db-test', function() {
    $db = getDatabase();

    $stmt = $db->prepare("SELECT * FROM Users");
    $stmt->execute();
    $result = $stmt->get_result();

    var_dump($result->fetch_row());
});

// Run Fat-Free
$f3->run();