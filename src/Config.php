<?php

/**
 *
 */
define("SITE", [
  "name" => "Auth em MVC com PHP",
  "desc" => "Aprenda a construir uma aplicação de autenticação em MVC com PHP do Jeito Certo!",
  "domain" => "localauth.com",
  "locale" => "pt_BR",
  "root" => "http://localhost/codigo-aberto"
]);

if($_SERVER["SERVER_NAME"] === "localhost") {
  require __DIR__."/Minify.php";
}

/**
 *
 */
define('DATA_LAYER_CONFIG', [
  "driver" => "mysql",
  "host" => "localhost",
  "port" => "3306",
  "dbname" => "auth",
  "username" => "root",
  "passwd" => "",
  "options" => [
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_CASE => PDO::CASE_NATURAL
  ]
]);

/**
 *
 */
define("SOCIAL", [
  "facebook_page" => "robsonvleite2",
  "facebook_author" => "robsonvleite",
  "facebook_appId" => "2193729837289",
  "twitter_creator" => "@robsonvleite",
  "twitter_site" => "@robsonvleite"
]);

/**
 *
 */
define("MAIL", [
    "host" => "smtp.gmail.com",
    "port" => "587",
    "user" => "",
    "passwd" => "",
    "from_name" => "",
    "from_email" => ""
]);

/**
 *
 */
define("FACEBOOK_LOGIN", []);

/**
 *
 */
define("GOOGLE_LOGIN", []);