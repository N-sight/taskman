<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 11.04.2020
 * Time: 10:13
 */

class Authcontroller extends Controller
{
    public static function className ()
    {
        return __CLASS__;
    }

    function __call($name, $params)
    {
        $c = self::className();
        System::error("Error: {$c} havent method: {$name}");
    }

    function  __construct ()
    {
        $this->layout = 'layout.php'; // в этой ветке контроллера Лейаут может быть индивидуальным
    }

    public function auth_login() // секция входа через страницу авторизации
    {
        if (count($_POST))
        {
            $action  = (isset($_POST['__action'])) ? $_POST['__action'] : 0;


            $username = Model::clear_query($_POST['login']);
            $password = Model::clear_query($_POST['password']);
            $remember = (isset($_POST['remember'])) ? (int) Model::clear_query($_POST['remember']) : 0;

            if ( $action === 'login' )
            {
                $user = new User();
                $result = $user->auth($username,$password,$remember);

                if ($result === true)
                {

                    System::set_message('success','OK: Enter system by login/password.');
                    header("Location: ".__HOME__);
                    die();

                }
                else
                {
                    System::set_message('error','Error : Login/password are incorrect.');
                    header("Location: ".__HOME__);
                    die();
                }
            }
        }
        return $this->render('task/list', [ ]);
    }

    public function auth_logout() // секция выхода через страницу авторизации (для снятия кукисов)
    {
        unset ($_SESSION['username']);
        unset ($_SESSION['password']);
        unset ($_COOKIE['username']);
        unset ($_COOKIE['password']);

        setcookie("username",' ',time()-60*60*24*7,'/');
        setcookie("password",' ',time()-60*60*24*7,'/');

        header('Location: /task/list');
        die();
    }

}
