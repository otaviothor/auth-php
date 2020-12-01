<?php

namespace Source\Controllers;

use stdClass;

class Web extends Controller
{
    public function __construct($router)
    {
        parent::__construct($router);

        if (!empty($_SESSION["user"])) {
            $this->router->redirect("app.home");
        }
    }

    public function login(): void
    {
        $head = $this->seo->optimize(
            "Faça login para continuar | " . site("name"),
            site("desc"),
            $this->router->route("web.login"),
            routeImage("Login")
        )->render();

        echo $this->view->render("theme/login", [
            "head" => $head
        ]);
    }

    public function register(): void
    {
        $head = $this->seo->optimize(
            "Crie sua conta | " . site("name"),
            site("desc"),
            $this->router->route("web.register"),
            routeImage("Register")
        )->render();

        $formUser = new stdClass();
        $formUser->first_name = null;
        $formUser->last_name = null;
        $formUser->email = null;

        echo $this->view->render("theme/register", [
            "head" => $head,
            "user" => $formUser
        ]);
    }

    public function forget(): void
    {
        $head = $this->seo->optimize(
            "Recupere sua senha | " . site("name"),
            site("desc"),
            $this->router->route("web.forget"),
            routeImage("Forget")
        )->render();

        $formUser = new stdClass();
        $formUser->first_name = null;
        $formUser->last_name = null;
        $formUser->email = null;

        echo $this->view->render("theme/forget", [
            "head" => $head
        ]);
    }

    public function reset($data): void
    {
        $head = $this->seo->optimize(
            "Crie sua nova senha | " . site("name"),
            site("desc"),
            $this->router->route("web.reset"),
            routeImage("Reset")
        )->render();

        echo $this->view->render("theme/reset", [
            "head" => $head
        ]);
    }

    public function error($data): void
    {
        $error = filter_var($data["errcode"], FILTER_VALIDATE_INT);

        $head = $this->seo->optimize(
            "Ooops {$error} | " . site("name"),
            site("desc"),
            $this->router->route("web.error", ["errcode" => $error]),
            routeImage($error)
        )->render();

        $formUser = new stdClass();
        $formUser->first_name = null;
        $formUser->last_name = null;
        $formUser->email = null;

        echo $this->view->render("theme/error", [
            "head" => $head,
            "error" => $error
        ]);
    }
}