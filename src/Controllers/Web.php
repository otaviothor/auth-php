<?php

namespace Source\Controllers;

use Source\Models\User;
use stdClass;


/**
 * Class Web
 * @package Source\Controllers
 */
class Web extends Controller
{

    /**
     * Web constructor.
     * @param $router
     */
    public function __construct($router)
    {
        parent::__construct($router);

        if (!empty($_SESSION["user"])) {
            $this->router->redirect("app.home");
        }
    }

    /**
     *
     */
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

    /**
     *
     */
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

        $socialUser = (!empty($_SESSION["facebook_auth"]) ? unserialize($_SESSION["facebook_auth"]) : (!empty($_SESSION["google_auth"]) ? unserialize($_SESSION["google_auth"]) : null));

        if ($socialUser) {
            $formUser->first_name = $socialUser->getFirstName();
            $formUser->last_name = $socialUser->getLastName();
            $formUser->email = $socialUser->getEmail();
        }

        echo $this->view->render("theme/register", [
            "head" => $head,
            "user" => $formUser
        ]);
    }

    /**
     *
     */
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

    /**
     * @param $data
     */
    public function reset($data): void
    {
        if (empty($_SESSION["forget"])) {
            flash("info", "Informe seu e-mail parar recuperar a senha!");
            $this->router->redirect("web.forget");
        }

        $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
        $forget = filter_var($data["forget"], FILTER_DEFAULT);

        $errForget = "Não foi possível recuperar a senha!";

        if (!$email || !$forget) {
            flash("error", $errForget);
            $this->router->redirect("web.forget");
        }

        $user = (new User())->find("email = :e AND forget = :f", "e={$email}&f={$forget}")->fetch();

        if (!$user)  {
            flash("error", $errForget);
            $this->router->redirect("web.forget");
        }

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

    /**
     * @param $data
     */
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