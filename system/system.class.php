<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 11.04.2020
 * Time: 11:13
 */
Class System
{
    protected static $user;

    public static function get_user ()
    {
        if (self::$user === NULL)
        {
            self::$user = new User();
            self::$user->auth_flow();
        }
        return static::$user;
    }

    // передача сообщений в сессиях , для отметки флагов выполнения операций.(см контроллеры)
    public static function set_message ($type,$message)
    {
        $_SESSION[$type] = $message;
    }

    public static function get_message ($type)
    {

        if (isset ($_SESSION[$type]) )
        {
            $value = $_SESSION[$type];
            unset ($_SESSION[$type]);
            return $value;
        }
        else
        {
            return NULL;
        }
    }

    public static function error ($errmsg = NULL) // аналог e404
    {
        self::set_message('error','Ошибка : '.$errmsg);
        header("Location: ".__HOME__);
        die();


    }
}
