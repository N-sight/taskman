<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 10.04.2020
 * Time: 23:15
 */
function class_autoloader($classname) // автозагрузка моделей и контроллеров
{
    $class_string = mb_substr($classname,0,mb_strlen($classname,'utf-8')-10, 'utf-8');
    $name = preg_replace('/([a-z])([A-Z])/', '$1_$2', $class_string);
    $file_name = "controller/".mb_strtolower($name,'utf-8').'_controller.php';

    if ( mb_substr($classname,-10,NULL, 'utf-8') === 'controller' )
    {


        if (file_exists($file_name))
        {
            require_once $file_name;
        }
        else
        {
            e404("Мы не нашли файл {$file_name}");
        }
    }
    else { // если это не контроллер - то может быть это модель?

        $maybemodel = 'model/'.mb_strtolower($classname,'utf-8'). '_model.php';
        if ( file_exists($maybemodel) )
        {
            require_once $maybemodel;
        }
        else
        {
            e404("Нет ни контроллера , ни модели соответствующие вызываемому методу или статическому объекту {$classname}. Искали тут {$file_name} и тут  {$maybemodel}");
        }
    }

}

function name2controller_class_name ($name)
{
    $pie = explode ('_',$name);
    $result ='';
    foreach ( $pie as $a)
    {
        $result .= ucfirst ($a);
    }
    $result = $result."controller";
    return $result;
}

function e404($error = NULL)
{
    header ("HTTP/1.1 404 Not Found");
    die("404 error, cause : {$error}");
}

function contains($content,$substr, $ignorecase=true)// выражения с символом & неловит!!!
{
    if ($ignorecase){
        $substr = mb_strtolower($substr);
        $content = mb_strtolower($content);
    }
    if (mb_strpos($content,$substr) !== false)
    {
        return true;
    }
    else
    {
        return false;
    }
}
