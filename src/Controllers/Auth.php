<?php

namespace Source\Controllers;

use Source\Models\User;
use function League\Plates\Util\id;

/**
 * Class Auth
 * @package Source\Controllers
 */
class Auth extends Controller
{
    /**
     * Auth constructor.
     * @param $router
     */
    public function __construct($router)
    {
        parent::__construct($router);
    }

    public function login($data): void
    {
       $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
       $password = filter_var($data["passwd"], FILTER_DEFAULT);

       if (!$email || !$password) {
           echo $this->ajaxResponse("message", [
               "type" => "alert",
               "message" => "Informe seu e-mail e senha para logar!"
           ]);
           return;
       }

       $user = (new User())->find("email = :email", "email={$email}")->fetch();
       if (!$user || !password_verify($password, $user->passwd)) {
           echo $this->ajaxResponse("message", [
               "type" => "error",
               "message" => "E-mail ou senha inválido!"
           ]);
           return;
       }

       $_SESSION["user"] = $user->id;
       echo $this->ajaxResponse("redirect", [
           "url" => $this->router->route("app.home")
       ]);
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

        $user = new User();
        $user->first_name = $data["first_name"];
        $user->last_name = $data["last_name"];
        $user->email = $data["email"];
        $user->passwd = $data["passwd"];

        if (!$user->save()) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => $user->fail()->getMessage()
            ]);
            return;
        }

        $_SESSION["user"] = $user->id;
        echo $this->ajaxResponse("redirect", [
            "url" => $this->router->route("app.home")
        ]);
    }
}