<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 11.04.2020
 * Time: 11:02
 */

class Taskcontroller extends Controller
{

    private $role;

    public static function className () //найти нужный класс который обрабатывает ту или иную функцию
    {
        return __CLASS__;
    }

    function __call($name, $params)
    {
        $c = self::className();
        System::error("Error: {$c} havent method: {$name}");
    }

    function __construct() // разрешаем заходить сюда простым пользователям
    {
        $u = (int) System::get_user()->role;
        $this->role = $u;
        if ($u == User::ROLE_ADMIN )
        {
            $this->layout = 'layout-adm.php'; // в этой ветке контроллера Лейаут будет альтернативным и своим :)
        }
        else
        {
            $this->layout = 'layout.php';
        }
    }

    public function task_list($page=1,$param='id-asc')
    {
        $task = new Task();
        $maxlines = $task->maxlines();
        $max_pages = (int) ceil ($maxlines/MAX_TASKS_IN_VIEW);
        $lines = $task->list($page,$param);

        if ($lines==false) e404('Invalid parameters. Can`t recieve from DB');


        if ($this->role != User::ROLE_ADMIN) {
            return $this->render('task/list', array('lines' => $lines, 'pages' => $max_pages, 'page' => $page, 'param' => $param));
        }
        else
        {
            return $this->render('task/list_adm', array('lines' => $lines, 'pages' => $max_pages, 'page' => $page, 'param' => $param));
        }
    }

    public function task_add()
    {
                /*if ((int) System::get_user()->role !== User::ROLE_ADMIN )
        {
            System::set_message('warning', 'Предупреждение : В целях безопасности функция создания рубрики разрешена только админам');
            header("Location: ".__HOME__);
            die();
        }*/

        $task = new Task();

        if (count($_POST)) {
            if ($_POST['__action'] === 'add')
            {
                $username = strip_tags($_POST['username']);
                $email =  strip_tags($_POST['email']);
                $job_desc = strip_tags($_POST['job_desc']);

                if (!preg_match('/.+@.+\..+/i', $email) && mb_strlen($email)>0 )
                {
                    System::set_message('error','Error : This '.$email.' is not email.');
                    header("Location: /task/add");
                    die();
                }

                if (mb_strlen($username)<1 || mb_strlen($email)<1 || mb_strlen($job_desc)<1 )
                {
                    System::set_message('error','Error : All field must be filled.');
                    header("Location: /task/add");
                    die();
                }


                if ( contains($username,'DROP') || contains($username,'TRUNCATE') ||
                    contains($email,'DROP') || contains($email,'TRUNCATE') ||
                    contains($job_desc,'DROP') || contains($job_desc,'TRUNCATE')
                )
                {
                    System::set_message('error','Error : You cannot use DROP & TRUNCATE words in security case.');
                    header("Location: /task/add");
                    die();
                }

                if (!$task->add($username,$email,$job_desc)) {
                    System::set_message('error', 'Error : Something goes wrong in adding new task.');
                    header("Location: /task/add");
                    die;
                }
                else{
                    System::set_message('success', "ОК : You added new task. ");
                    header("Location: /task/list");
                    die();
                }

            }
        }
        return $this->render("task/add", array(NULL));
    }

    public function task_edit($id)
    {
        $task = new Task();
        $line = $task->get_by_id($id);

        if (isset($_POST['__action']))
        {
            if ($_POST['__action'] === 'edit')
            {
                if ($this->role != User::ROLE_ADMIN) {
                    System::set_message('warning', 'Warning : Only admins cant edit tasks.');
                    header("Location: /task/list");
                    die();
                }

                $username = strip_tags ($_POST['username']);
                $email = strip_tags ($_POST['email']);
                $desc = strip_tags ($_POST['job_desc']);
                $is_checked = strip_tags ($_POST['is_completed']);
                $old_desc = $_POST['old_desc'];
                $status = ($is_checked === 'on') ? 1 : 0;

                if (!preg_match('/.+@.+\..+/i', $email) && mb_strlen($email)>0 )
                {
                    System::set_message('error','Error : This '.$email.' is not email.');
                    header("Location: /task/edit/".$id);
                    die();
                }

                if (mb_strlen($username)<1 || mb_strlen($email)<1 || mb_strlen($desc)<1 )
                {
                    System::set_message('error','Error : All field must be filled.');
                    header("Location: /task/edit/".$id);
                    die();
                }

                if ( contains($username,'DROP') || contains($username,'TRUNCATE') ||
                    contains($email,'DROP') || contains($email,'TRUNCATE') ||
                    contains($desc,'DROP') || contains($desc,'TRUNCATE')
                )
                {
                    System::set_message('error','Error : You cannot use DROP & TRUNCATE words in security case.');
                    header("Location: /task/edit/".$id);
                    die();
                }

                if ($task->edit($id,$username,$email,$desc,$status,$old_desc))
                {
                    System::set_message('success', "ОК : Edit operation successful.");
                    header("Location: /task/list");
                    die();
                }
                else
                {
                    System::set_message('error', 'Error : Something goes wrong in editing new task.');
                    header("Location: /task/edit/".$id);
                    die;
                }

            }
        }

        return $this->render ("task/edit", array ('line' => $line) );


    }

}
