<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 11.04.2020
 * Time: 10:27
 */
class Controller
{
    protected $layout = 'layout.php';

    function __call($name, $params)
    {
        e404("Нет метода $name");
    }

    public function render($view_name,$data = array(), $with_layout = true)
    {
        ob_start();
        $lib = "template/".$view_name.".php";

        foreach ($data as $key => $value) // передаем во вьюшку переменные
        {
            $$key = $value;
        }

        if( file_exists($lib))
        {
            require_once ("$lib");
        }
        else
        {
            e404('404 - нет такой вьюшки :'.$lib);
        }
        $content = ob_get_contents();
        ob_end_clean();

        if ( ($with_layout)){
            ob_start();
            require_once ('template/layout/'.$this->layout);
            $content = ob_get_contents();
            ob_end_clean();
        }

        return $content;
    }
}