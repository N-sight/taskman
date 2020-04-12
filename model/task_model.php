<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 11.04.2020
 * Time: 12:20
 */
class Task extends Model
{

    public $id;
    public $username;
    public $email;
    public $description;
    public $is_completed;
    public $edited;

    function  __construct ()
    {
        if (static::$db == NULL) static::$db = static::get_db();
    }

    public static function tableName()
    {
        return 'tasks';
    }

    public function list ($page,$param) // возвращает список рубрик
    {
        $start = ($page-1)*MAX_TASKS_IN_VIEW;
        $end = MAX_TASKS_IN_VIEW;

        $pie = explode('-',$param);
        if (($pie[0] == 'id' || $pie[0] == 'username' || $pie[0] == 'email' || $pie[0] == 'is_completed') && ($pie[1] == 'asc' || $pie[1] == 'desc') )
        {

            $query = ($pie[1] == 'asc') ? "SELECT * FROM `tasks` ORDER BY $pie[0] ASC LIMIT $start,$end" : "SELECT * FROM `tasks` ORDER BY $pie[0] DESC LIMIT $start,$end";
            $send = mysqli_query(self::get_db(), $query);
            if (!$send) e404(mysqli_error(self::get_db()));
            $all = mysqli_fetch_all($send, MYSQLI_ASSOC);
        }
        else return false;

        return $all;

    }

    public function add($username,$email,$job_desc)
    {
        $username = static::clear_query($username);
        $email = static::clear_query($email);
        $job_desc = static::clear_query($job_desc);

        $query = "INSERT INTO `tasks` (`username`,`email`,`description`,`is_completed`,`edited`) VALUES ('$username','$email','$job_desc',0,0)";
        $send = mysqli_query(static::get_db(), $query);
        if (!$send)
        {
            return false;
        }
        return true;
    }

    public function maxlines()
    {
        $query = "SELECT COUNT(*) FROM `tasks`";
        $send = mysqli_query(self::get_db(), $query);
        if (!$send) e404 (mysqli_error(self::get_db()));
        $all = mysqli_fetch_all($send,MYSQLI_ASSOC);
        return (int) $all[0]['COUNT(*)'];
    }

    public function get_by_id($id)
    {
        $query = "SELECT * FROM `".self::tableName()."` WHERE `id` = '{$id}' LIMIT 1";
        $send = mysqli_query(static::get_db(),$query);
        if (!$send) e404 (mysqli_error(self::get_db()));
        $all = mysqli_fetch_all($send, MYSQLI_ASSOC);

        $this->id = $all[0]['id'];
        $this->username = $all[0]['username'];
        $this->email = $all[0]['email'];
        $this->description = $all[0]['description'];
        $this->is_completed = $all[0]['is_completed'];
        $this->edited = $all[0]['edited'];
        return $all[0];
    }

    public function edit ($id,$username,$email,$job_desc,$status,$old_desc)
    {
        $flag = ($old_desc === $job_desc) ? 0 : 1; // флажок изменения текста задачи
        $id = static::clear_query($id);
        $username = static::clear_query($username);
        $email = static::clear_query($email);
        $job_desc = static::clear_query($job_desc);
        $status = static::clear_query($status);
        $old_desc = static::clear_query($old_desc);


        if ($flag == 1) $query = "UPDATE `".self::tableName()."` SET `username` = '".($username)."', `email` = '".($email)."', `description` = '".($job_desc)."', `is_completed` = ".($status).", `edited` = 1 WHERE `id`= $id";
        else $query = "UPDATE `".self::tableName()."` SET `username` = '".($username)."', `email` = '".($email)."', `description` = '".($job_desc)."', `is_completed` = ".($status).", `edited` = 0 WHERE `id`= $id";

        $send = mysqli_query(static::get_db(), $query);
        if (!$send)
        {
            return false;
        }
        else
            return true;
    }
}