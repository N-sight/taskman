<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 10.04.2020
 * Time: 18:54
 */
date_default_timezone_set('Europe/Moscow');
error_reporting(E_ALL);
ini_set('display_errors','On');
session_start();
require_once ('helper/const.php'); // Константы тут
require_once ('helper/functions.php'); // Хелперы тут
require_once ('system/system.class.php'); // Системный класс
require_once ('system/controller.class.php'); // Рендер класса
require_once ('system/model.class.php'); // Части работы с БД

$default_controller = 'task';
$default_action = 'list';
$default_reqid = NULL;
$default_param = NULL;

spl_autoload_register('class_autoloader'); // вызывает функцию class_autoloader из доступных где идет подгрузка нужного файла

$link = Model::get_db();

// обработка ЧПУ
if (isset($_GET['route']))
{
    $g = strip_tags($_GET['route']);
    $pie = explode('/',$g);

    if (isset($pie[0]) && $pie[0] !=='')
    {
        $controller = $pie[0];
    }
    else
    {
        $controller = $default_controller;
    }

    if (isset($pie[1]) && $pie[1] !=='')
    {
        $controller_action = $pie[1];
    }
    else
    {
        $controller_action = $default_action;
    }

    if (isset($pie[2]) && $pie[2] !=='')
    {
        $request_id = (int) $pie[2];
    }
    else
    {
        $request_id = $default_reqid;
    }

    if (isset($pie[3]) && $pie[3] !=='')
    {
        $param =  $pie[3];
    }
    else
    {
        $param = $default_param;
    }

}
else
{
    $controller = $default_controller;
    $controller_action = $default_action;;
    $request_id = $default_reqid;
    $param = $default_param;
}

$controller_class_name = name2controller_class_name ($controller);
$controller_function_name = $controller."_".$controller_action;
$controller_object = new $controller_class_name();

if ( $request_id !== NULL)
{
    if ($param !== NULL)
        $result = $controller_object -> $controller_function_name($request_id,$param);
    else
        $result = $controller_object -> $controller_function_name($request_id);
}
else
{
    $result = $controller_object -> $controller_function_name();
}


if ( $result ) echo $result;
mysqli_close($link);
die();
