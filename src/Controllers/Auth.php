<?php

namespace Source\Controllers;

use Source\Models\User;

class Auth extends Controller
{
    public function __construct($router)
    {
        parent::__construct($router);
    }

    public function register($data): void
    {
        $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
        if (in_array("", $data)) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "Preencha todos os campos para cadastrar-se!"
            ]);
            return;
        }

        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "Favor, informe um e-mail válido para continuar!"
            ]);
            return;
        }

        $checkUserEmail = (new User())->find("email = :e", "e={$data["email"]}")->count();

        if ($checkUserEmail) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "Já existe um usuário cadastrado com esse e-mail!"
            ]);
            return;
        }

        $user = new User();
        $user->first_name = $data["first_name"];
        $user->last_name = $data["last_name"];
        $user->email = $data["email"];
        $user->passwd = password_hash($data["passwd"], PASSWORD_DEFAULT);
        $user->save();

        $_SESSION["user"] = $user->id;

        echo $this->ajaxResponse("redirect", [
            "url" => $this->router->route("app.home")
        ]);
    }

    public function login()
    {

    }
}