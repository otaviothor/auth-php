<?php

namespace Source\Controllers;

use Exception;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use Source\Models\User;
use Source\Support\Email;
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

    /**
     * @param $data
     */
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

        /**
         * Social Validate
         */
        $this->socialValidate($user);

       $_SESSION["user"] = $user->id;
       echo $this->ajaxResponse("redirect", [
           "url" => $this->router->route("app.home")
       ]);
    }

    /**
     * @param $data
     */
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

        /**
         * Social Validate
         */
        $this->socialValidate($user);

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

    /**
     * @param $data
     */
    public function forget($data): void
    {
        $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo $this->ajaxResponse("message", [
                "type" => "alert",
                "message" => "Informe o seu e-mail para recuperar a senha!"
            ]);
            return;
        }

        $user = (new User())->find("email = :email", "email={$email}")->fetch();
        if (!$user) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "O e-mail informado não está cadastrado!"
            ]);
            return;
        }

        $user->forget = (md5(uniqid(rand(), true)));
        $user->save();

        $_SESSION["forget"] = $user->id;

        $email = new Email();
        $email->add(
            "Recupere sua senha | ". site("name"),
            $this->view->render("emails/recover", [
                "user" => $user,
                "link" => $this->router->route("web.reset", [
                    "email" => $user->email,
                    "forget" => $user->forget
                ])
            ]),
            "{$user->first_name} {$user->last_name}",
            $user->email
        )->send();

        flash("success", "Enviamos um link de recuperação para seu e-mail");

        echo $this->ajaxResponse("redirect", [
            "url" => $this->router->route("web.forget")
        ]);
    }

    /**
     * @param $data
     */
    public function reset($data): void
    {
        if (empty($_SESSION["forget"]) || !$user = (new User())->findById($_SESSION["forget"])) {
            flash("error", "Não foi possível recuperar, tente novamente!");
            echo $this->ajaxResponse("redirect", [
                "url" => $this->router->route("web.forget")
            ]);
            return;
        }

        if (empty($data["password"]) || empty($data["password_re"])) {
            echo $this->ajaxResponse("message", [
                "type" => "alert",
                "message" => "Informe e repita sua nova senha!"
            ]);
            return;
        }

        if ($data["password"] !== $data["password_re"]) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "As senhas não conferem!"
            ]);
            return;
        }

        $user->passwd = $data["password"];
        $user->forget = null;

        if (!$user->save()) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => $user->fail()->getMessage()
            ]);
            return;
        }

        unset($_SESSION["forget"]);
        flash("success", "Sua senha foi atualizada com sucesso!");
        echo $this->ajaxResponse("redirect", [
            "url" => $this->router->route("web.login"),
        ]);
    }

    public function facebook(): void
    {
        $facebook = new Facebook(FACEBOOK_LOGIN);
        $error = filter_input(INPUT_GET, "error", FILTER_SANITIZE_STRIPPED);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_STRIPPED);

        if (!$error && !$code) {
            $auth_url = $facebook->getAuthorizationUrl(["scope" => "email"]);
            header("Location: {$auth_url}");
            return;
        }

        if ($error) {
            flash("error", "Não foi possível logar com o Facebook!");
            $this->router->redirect("web.login");
        }

        if ($code && empty($_SESSION["facebook_auth"])) {
            try {
                $token = $facebook->getAccessToken("authorization_code", ["code" => $code]);
                $_SESSION["facebook_auth"] = serialize($facebook->getResourceOwner($token));
            } catch (Exception $exception) {
                flash("error", "Não foi possível logar com o Facebook!");
                $this->router->redirect("web.login");
            }
        }

        /** @var $facebook_user FacebookUser */
        $facebook_user = unserialize($_SESSION["facebook_auth"]);
        $userById = (new User())->find("facebook_id = :id", "id={$facebook_user->getId()}")->fetch();

        if ($userById) {
            unset($_SESSION["facebook_auth"]);
            $_SESSION["user"] = $userById->id;
            $this->router->redirect("app.home");
        }

        $userByEmail = (new User())->find("email = :email", "email={$facebook_user->getEmail()}")->fetch();

        if ($userByEmail) {
            flash("info", "Olá {$facebook_user->getFirstName()}, faça login para conectar seu Facebook!");
            $this->router->redirect("web.login");
        }

        $link = $this->router->route("web.login");
        flash(
            "info",
            "Olá {$facebook_user->getFirstName()}, <b><a title='Fazer Login' href='{$link}'>se já tem uma conta, clique em fazer login</a></b>, ou complete seu cadastro!"
        );
        $this->router->redirect("web.register");
    }

    public function google(): void
    {
        $google = new Google(GOOGLE_LOGIN);
        $error = filter_input(INPUT_GET, "error", FILTER_SANITIZE_STRIPPED);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_STRIPPED);

        if (!$error && !$code) {
            $auth_url = $google->getAuthorizationUrl();
            header("Location: {$auth_url}");
            return;
        }

        if ($error) {
            flash("error", "Não foi possível logar com o Google!");
            $this->router->redirect("web.login");
        }

        if ($code && empty($_SESSION["google_auth"])) {
            try {
                $token = $google->getAccessToken("authorization_code", ["code" => $code]);
                $_SESSION["google_auth"] = serialize($google->getResourceOwner($token));
            } catch (Exception $exception) {
                flash("error", "Não foi possível logar com o Google!");
                $this->router->redirect("web.login");
            }
        }

        /** @var $google_user GoogleUser */
        $google_user = unserialize($_SESSION["google_auth"]);
        $userById = (new User())->find("google_id = :id", "id={$google_user->getId()}")->fetch();

        if ($userById) {
            unset($_SESSION["google_auth"]);
            $_SESSION["user"] = $userById->id;
            $this->router->redirect("app.home");
        }

        $userByEmail = (new User())->find("email = :email", "email={$google_user->getEmail()}")->fetch();

        if ($userByEmail) {
            flash("info", "Olá {$google_user->getFirstName()}, faça login para conectar seu Google!");
            $this->router->redirect("web.login");
        }

        $link = $this->router->route("web.login");
        flash(
            "info",
            "Olá {$google_user->getFirstName()}, <b><a title='Fazer Login' href='{$link}'>se já tem uma conta, clique em fazer login</a></b>, ou complete seu cadastro!"
        );
        $this->router->redirect("web.register");
    }

    public function socialValidate(User $user): void
    {
        /**
         * Facebook
         */
         if (!empty($_SESSION["facebook_auth"])) {
             /** @var $facebook_user FacebookUser */
             $facebook_user = unserialize($_SESSION["facebook_auth"]);

             $user->facebook_id = $facebook_user->getId();
             $user->photo = $facebook_user->getPictureUrl();
             $user->save();

             unset($_SESSION["facebook_auth"]);
         }

        /**
         * Google
         */
        if (!empty($_SESSION["google_auth"])) {
            /** @var $google_user GoogleUser */
            $google_user = unserialize($_SESSION["google_auth"]);

            $user->google_id = $google_user->getId();
            $user->photo = $google_user->getAvatar();
            $user->save();

            unset($_SESSION["google_auth"]);
        }
    }
}












