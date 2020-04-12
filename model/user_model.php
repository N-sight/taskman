<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 12.04.2020
 * Time: 0:24
 */
class User extends Model
{
    const ROLE_USER = 1;
    const ROLE_ADMIN = 10;

    public static $roles = [
        self::ROLE_USER => 'Пользователь',
        self::ROLE_ADMIN => 'Администратор'
    ];

    public $id;
    public $login;
    public $password;
    public $role;

    function  __construct ()
    {
        if (static::$db == NULL) static::$db = static::get_db();
    }

    public static function className () //найти нужный класс который обрабатывает ту или иную функцию
    {
        return __CLASS__;
    }

    public static function tableName()
    {
        return 'users';
    }

    public function get_by_username($username) // загрузить ряд по юзернейму
    {
        $query = "SELECT * FROM `".self::tableName()."` WHERE `login` = '{$username}' LIMIT 1";
        $send = mysqli_query(static::get_db(),$query);
        $all = mysqli_fetch_all($send, MYSQLI_ASSOC);

        $this->login = $all[0]['login'];
        $this->password = $all[0]['password'];
        $this->role = $all[0]['role'];
        return $all[0];

    }

    public function auth_flow()   // авторизация по кукам и сессии через запрос класса System
    {
        if ( (isset($_SESSION['username'])) && (isset($_SESSION['password'])))
        {
            $username = $_SESSION['username'];
            $password = $_SESSION['password'];

            $this->get_by_username($username); // загружаем поля пользователя по имени пользователя

            if ($this->password === $password )  // проверка напрямую ,потому что пароль в куках и сессиях хранится в зашифрованном виде
            {
                return true;
            }
            else
            {
                $this->id = NULL;
                $this->role = NULL;
                $this->login = NULL;
                $this->password = NULL;
                return false;
            }
        }
        elseif ( (isset($_COOKIE['username'])) && (isset($_COOKIE['password'])) )
        {
            $username = $_COOKIE['username'];
            $password = $_COOKIE['password'];
            $this->get_by_username($username); // функция загрузки полей пользователя по имени пользователя.


            if ($this->password === $password ) // проверка напрямую ,потому что пароль хранится в зашифрованнном виде
            {
                $_SESSION['username'] = $_COOKIE['username'];
                $_SESSION['password'] = $_COOKIE['password'];
                System::set_message('success','Enter by cookies.');
                return true;
            }
            else
            {
                $this->id = NULL;
                $this->role = NULL;
                $this->login = NULL;
                $this->password = NULL;
                return false;
            }
        }
        return false;
    }

    public function auth($username,$password,$remember = false) // авторизация через ввод формы
    {
        $this->get_by_username($username); // загрузили поля по username
        if ($this->password === $password) // проверяем тут пароль
        {
            $_SESSION['username'] = $this->login;
            $_SESSION['password'] = $this->password;
            if ($remember) // $remember= галочка запомнить меня.
            {
                setcookie("username",$this->login,time()+60*60*24*7,'/'); // на неделю!!!
                setcookie("password",$this->password,time()+60*60*24*7,'/');
            }
            return true;
        }
        else
        {
            return false;
        }
    }

}