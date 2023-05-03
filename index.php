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

// Create an F3 (Fat-Free Framework) object
$f3 = Base::instance();

$f3->route('GET /', function () {
    echo "<h1>Hello world!</h1>";
});

// Run Fat-Free
$f3->run();