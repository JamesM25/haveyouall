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
$con = new Controller($f3);
$data = new DataLayer();

const ICON_PATH = 'images/mbox-icon.png';

const STYLESHEETS = [

    "https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css",
    "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css",
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css",
    "styles/style.css"
];

const SCRIPTS = [
    "https://code.jquery.com/jquery-3.3.1.slim.min.js",
    "https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js",
    "https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
];

const FILTER_TYPES = [
    "votes"=>"Votes",
    "replies"=>"Replies",
    "views"=>"Posts.Views"
];

$f3->set('favicon', ICON_PATH);
$f3->set('stylesheets', STYLESHEETS);
$f3->set('scripts', SCRIPTS);
$f3->set("userSearch", "");

// Default route
$f3->route('GET /', function () {
    $GLOBALS['con']->home();
});

$f3->route('GET|POST /login', function () {
    $GLOBALS['con']->login();
});

$f3->route('GET /logout', function () {
    $GLOBALS['con']->logout();
});

$f3->route('GET|POST /signup', function () {
    $GLOBALS['con']->signup();
});

$f3->route('GET|POST /create-post', function() {
    $GLOBALS['con']->createPost();
});

$f3->route('GET|POST /report/@id', function($f3, $params) {
    $GLOBALS['con']->reportPost($params['id']);
});


$f3->route('GET|POST /post/@id', function($f3, $params) {
    $GLOBALS['con']->post($params['id']);
});

$f3->route('GET|POST /vote/@id', function($f3, $params) {
    $GLOBALS['con']->vote($params['id']);
});

$f3->route('GET /search', function () {
    $GLOBALS['con']->search();
});

$f3->route('GET /remove/@id', function($f3, $params) {
    $GLOBALS['con']->removePost($params['id']);
});

$f3->route('GET /admin', function () {
    $GLOBALS['con']->admin();
});

// Run Fat-Free
$f3->run();