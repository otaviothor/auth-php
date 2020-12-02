<?php

ob_start();
session_start();

require __DIR__ . "/vendor/autoload.php";
use CoffeeCode\Router\Router;

$router = new Router(site());
$router->namespace("Source\Controllers");

/**
 * Web
 */
$router->group(null);
$router->get("/", "Web:login", "web.login");
$router->get("/cadastrar", "Web:register", "web.register");
$router->get("/recuperar", "Web:forget", "web.forget");
$router->get("/senha/{email}/{forget}", "Web:reset", "web.reset");

/**
 * Auth
 */
$router->group(null);
$router->post("/login", "Auth:login", "auth.login");
$router->post("/register", "Auth:register", "auth.register");
$router->post("/forget", "Auth:forget", "auth.forget");
$router->post("/reset", "Auth:reset", "auth.reset");

/**
 * Auth social
 */

/**
 * Profile
 */
$router->group("me");
$router->get("/", "App:home", "app.home");
$router->get("/sair", "App:logoff", "app.logoff");

/**
 * Errors
 */
$router->group("ops");
$router->get("/{errcode}", "Web:error", "web.error");

/**
 * Route proccess
 */
$router->dispatch();

/**
 * Errors proccess
 */
if ($router->error()) {
    $router->redirect("web.error", [
        "errcode" => $router->error()
    ]);
}


ob_end_flush();