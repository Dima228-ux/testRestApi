<?php

require_once './core/Router.php';

$url = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];


$router = new Router($url, $method);
$router->route();