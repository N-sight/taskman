<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 12.04.2020
 * Time: 14:22
 */

class Model
{
    public static $db = NULL;

    public static function get_db()
    {
        if (self::$db == NULL) {
            $link = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS_WORD, MYSQL_DB)
            or e404('Error: ' . mysqli_error($link));

            if (!mysqli_set_charset($link, 'UTF8MB4')) {
                e404('Error: ' . mysqli_error($link));
            }
            self::$db = $link;
        }
        return self::$db;
    }

    public static function clear_query ($query) // пропускаем запрос через real_escape_string
    {
        return mysqli_real_escape_string(self::get_db(),$query);
    }

}