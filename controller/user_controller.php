<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 12.04.2020
 * Time: 0:00
 */

class Usercontroller extends Controller
{
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
        if ((int) System::get_user()->role == User::ROLE_ADMIN )
        {
            $this->layout = 'layout_adm.php'; // в этой ветке контроллера Лейаут будет альтернативным и своим :)

        }

        if (System::get_user()->role == NULL ) // любой неавторизированный пользователь
        {
            header("Location: /auth/login");
            die();
        }
    }


    public function users_add ()
    {
        $function_name = __FUNCTION__.'(';
        $arg_list = func_get_args();
        for ($i=0;$i<count($arg_list);$i++) $function_name .=  ' '.$arg_list[$i];
        $function_name .= ')';
        $userlog = new Loguser();
        $userlog->action = $function_name;
        $userlog->add_action();

        if ((int) System::get_user()->role !== User::ROLE_ADMIN )
        {
            System::set_message('error','Ошибка : Пользователям не разрешено добавлять новых пользователей :)');
            header("Location: /users/list");
            die();
        }

        $outadd = new User();
        if (count($_POST))
        {
            if ($_POST['__action'] === 'add')
            {
                $username = Model::clear_query($_POST['name']); // здесь вкрутить очистку от вирусов
                $password1 = Model::clear_query($_POST['pass1']);
                $password2 = Model::clear_query($_POST['pass2']);
                $email = trim(Model::clear_query($_POST['email']));
                $role = Model::clear_query($_POST['role']);

                if ($password1 != $password2)
                {
                    System::set_message('error','Ошибка : Пароли не совпадают');
                    header("Location: /users/add");
                    die();
                }
                else $password = $password1;

                if (!preg_match('/.+@.+\..+/i', $email))
                {
                    System::set_message('error','Ошибка : Вводите корректный мейл. Этот - '.$email.' не похож');
                    header("Location: /users/add");
                    die();
                }

                if (mb_strlen($password)<5)
                {
                    System::set_message('error','Ошибка : Ваш пароль слишком короткий. Принимаются пароли длиннее 5 символов');
                    header("Location: /users/add");
                    die();
                }

                //Проверить уникальность мейла и логина
                if ( $outadd->is_double_tab("email",$email))
                {
                    System::set_message('error','Ошибка : Пользователь с email-ом '.$email.' уже зарегистрирован в сиситеме. Выберете другой email');
                    header("Location: /users/add");
                    die();
                }

                if ( $outadd->is_double_tab("username",$username))
                {
                    System::set_message('error','Ошибка : Пользователь с логином '.$username.' уже зарегистрирован в сиситеме. Выберете другой ник');
                    header("Location: /users/add");
                    die();
                }

                if ($password == '12345' || $password == '54321' || $password == 'qwerty')
                {
                    System::set_message('error','Ошибка : Смеетесь что-ли? Такие простые пароли мы не разрешаем использовать.');
                    header("Location: /users/add");
                    die();
                }

                $outadd->username = $username;
                $outadd->role = $role;
                $outadd->email = $email;
                $outadd->create_password($password);


                if ( $outadd->add()!== Model::CREATE_FAILED )
                {
                    if  ( !( ($outadd->error === void) || ($outadd->error === repeats) )  )
                    {
                        $outadd->checked_update(0);
                        $hash = md5(date("d-m-Y H:i:s").$email.base64_encode(date('M')).$username);
                        $outadd->checkup_update($hash);

                        $mailer = new FreakMailer();
                        if (!$mailer->send_mail(
                            'Подтверждение доступа',
                            'Здравствуйте <br>
                                     Вы (или кто-то) зарегистрировались на сайте Fan-spotter.ru и указали этот email<br>
                                     Для подтверждения вашей почты пройдите пожалуйста по ссылке:<br>
                                     <a href="https://fan-spotter.ru/auth/acceptmail/'. $outadd->id.'/'.$hash.'">https://fan-spotter.ru/auth/acceptmail/'. $outadd->id.'/'.$hash.'</a><br>
                                     Или введите код восстановления вручную. '.$hash.'<br>
                                     Адрес ввода для ввода пароля https://fan-spotter.ru/auth/acceptmail/'. $outadd->id.'/ <br>
                                     Если вы не регистрировались на сайте fan-spotter.ru - просим проигнорировать это письмо.<br><br>

                                     C уважением, команда Fan-Spotter.',
                            $outadd->email,
                            $outadd->username
                        ))
                        {
                            $userlog->action = 'Не удалось отправить письмо восстановления пароля на адрес '. $outadd->email;
                            $userlog->add_action();
                        }


                        System::set_message('success',"<small>ОК: Пользователь добавлен, на $email выслано письмо для активации аккаунта.</small>");
                        header("Location: /users/article/{$outadd->id}");
                        die();
                    }
                    else
                    {
                        System::set_message('error','Пользователя добавить не удалось, '.$outadd->errmsg);
                        header("Location: /users/list");
                        die();
                    }

                }
                else
                {

                    System::set_message('error','Пользователя добавить не удалось, '.$outadd->errmsg);
                    header("Location: /users/list");
                    die();
                }
            }
        }

        $roles = User::$roles;
        return $this->render("users/add_users", array( 'outadd' => $outadd , 'roles' => $roles ));
    }

    public function users_list() // добавлено ветвление на юзера и админа в модели
    {
        $function_name = __FUNCTION__.'(';
        $arg_list = func_get_args();
        for ($i=0;$i<count($arg_list);$i++) $function_name .=  ' '.$arg_list[$i];
        $function_name .= ')';
        $userlog = new Loguser();
        $userlog->action = $function_name;
        $userlog->add_action();

        $users = User::all();
        return $this->render('users/index_users', array('users' => $users ) );
    }

    public function users_article ($id)
    {
        $function_name = __FUNCTION__.'(';
        $arg_list = func_get_args();
        for ($i=0;$i<count($arg_list);$i++) $function_name .=  ' '.$arg_list[$i];
        $function_name .= ')';
        $userlog = new Loguser();
        $userlog->action = $function_name;
        $userlog->add_action();

        if ( System::get_user()->id != $id && (int) System::get_user()->role !== User::ROLE_ADMIN )
        {
            System::set_message('Error', 'Ошибка : В чужие аккаунты лезть нельзя');
            header("Location: /users/list");
            die();
        }

        $out = new User ($id);

        if (!$out->is_loaded() )
        {
            System::set_message('error','Ошибка : Пользователя не удалось найти по его идентификатору');
            header("Location: /users/list");
            die();
        }

        return $this->render("users/article_users", array('out' => $out));
    }

    public function users_delete ($id)
    {
        $function_name = __FUNCTION__.'(';
        $arg_list = func_get_args();
        for ($i=0;$i<count($arg_list);$i++) $function_name .=  ' '.$arg_list[$i];
        $function_name .= ')';
        $userlog = new Loguser();
        $userlog->action = $function_name;
        $userlog->add_action();

        if ((int) System::get_user()->role !== User::ROLE_ADMIN ) {
            System::set_message('error', 'Ошибка : Пользователям не разрешено удалять пользователей :)');
            header("Location: /users/list");
            die();
        }

        $out_del = new User ($id);
        if (!$out_del->is_loaded() )
        {
            System::set_message('error','Ошибка : Пользователя не удалось найти по его идентификатору');
            header('Location: /users/list');
            die();
        }

        // здесь логика ошибок.
        if ($id == 12) $out_del->error = first; // первого админа удалить нельзя.

        if (System::get_user()->id == $id) //запись под которой зашли удалить нельзя
        {
            $out_del->error = repeats;
        }


        $sections = Section::all();

        foreach ($sections as $item)
        {
            if ($item->user_id == $id )
            {
                System::set_message('error','Ошибка : Нельзя удалить пользователя, когда у него существуют рубрики. Например вот эта: '.$item->name);
                header('Location: /sections/list');
                die();
            }
        }


        if ( ($out_del->error !== first) && ($out_del->error !== repeats))
        {
            $out_del->error = void;
        }

        if (count($_POST) > 0)
        {
            if ($_POST['__action'] === 'delete')
            {
                $out_del->delete();

                if ($out_del->error === success)
                {
                    System::set_message('success',"ОК: Пользователя id={$id} успешно удалили из системы");
                    header('Location: /users/list');
                    die();
                }
                else
                {
                    System::set_message('error','Ошибка , с удалением пользователя id='.$id.'  что-то пошло не так');
                    header('Location: /users/list');
                    die();
                }
            }
        }



        return $this->render("users/delete_users",array( 'out_del' => $out_del ) );
    }

    public function users_change ($id)
    {
        $function_name = __FUNCTION__.'(';
        $arg_list = func_get_args();
        for ($i=0;$i<count($arg_list);$i++) $function_name .=  ' '.$arg_list[$i];
        $function_name .= ')';
        $userlog = new Loguser();
        $userlog->action = $function_name;
        $userlog->add_action();

        $change_password = 1; // флажок изменения пароля.
        $out_edit = new User ($id);

        if (!$out_edit->is_loaded() )
        {
            System::set_message('error','Ошибка : Пользователя не удалось найти по его идентификатору');
            header("Location: /users/list");
            die();
        }

        if ( System::get_user()->id != $id && (int) System::get_user()->role !== User::ROLE_ADMIN )
        {
            System::set_message('Error', 'Ошибка : В чужие аккаунты лезть нельзя');
            header("Location: /users/list");
            die();
        }

        if (count($_POST)) // обработчики событий
        {
            if ($_POST['__action'] === 'edit')
            {
                $username = Model::clear_query($_POST['name']);
                $pass1 = Model::clear_query($_POST['pass1']);
                $pass2 = Model::clear_query($_POST['pass2']);
                $email = trim(Model::clear_query($_POST['email']));

                if ( ($username =='') || ($email =='')) // поля пароля могут оставаться пустыми.
                {
                    System::set_message('error', 'Ошибка : Пустые данные вводить нельзя');
                    header("Location: /users/change/{$id}");
                    die();
                }

                if ( ($username =='admin') )
                {
                    System::set_message('error', 'Ошибка : Логин admin - заблокирован по технологическим причинам');
                    header("Location: /users/change/{$id}");
                    die();
                }


                if ($pass1 != $pass2)
                {
                    System::set_message('error','Ошибка : Пароли не совпадают');
                    header("Location: /users/change/{$id}");
                    die();
                }
                else $password = $pass1;

                if ( ($pass1 =='') && ($pass2 ==''))
                {
                    $change_password = 0; // пароль не меняется
                }

                if (mb_strlen($password)<5 && $password != "")
                {
                    System::set_message('error','Ошибка : Ваш пароль слишком короткий. Принимаются пароли длиннее 5 символов');
                    header("Location: /users/change/{$id}");
                    die();
                }

                //Проверить уникальность мейла и логина
                if ( $out_edit->is_double_tab("email",$email) && $out_edit->email != $email)
                {
                    System::set_message('error','Ошибка : Пользователь с email-ом '.$email.' уже зарегистрирован в сиситеме. Выберете другой email');
                    header("Location: /users/change/{$id}");
                    die();
                }

                if ( $out_edit->is_double_tab("username",$username) && $out_edit->username != $username)
                {
                    System::set_message('error','Ошибка : Пользователь с логином '.$username.' уже зарегистрирован в сиситеме. Выберете другой ник');
                    header("Location: /users/change/{$id}");
                    die();
                }

                if (!preg_match('/.+@.+\..+/i', $email))
                {
                    System::set_message('error','Ошибка : Вводите корректный мейл. Этот - '.$email.' не похож');
                    header("Location: /users/change/{$id}");
                    die();
                }

                $old_username = $out_edit->username;
                $old_password = $out_edit->password;
                $old_email    = $out_edit->email;


                $out_edit->username = $username;
                $out_edit->edit();
                if ($out_edit->error != success)
                {
                    System::set_message('error','Ошибка : Логин пользователя изменить не удалось');
                    $out_edit->username = $old_username;
                    $out_edit->edit();
                    header("Location: /users/change/{$id}");
                    die();
                }


                $out_edit->email = $email;
                $out_edit->edit();
                if ($out_edit->error != success)
                {
                    System::set_message('error','Ошибка : Email пользователя изменить не удалось');
                    $out_edit->email = $old_email;
                    $out_edit->edit();
                    header("Location: /users/change/{$id}");
                    die();
                }

                if ($change_password)
                {
                    $out_edit->create_password($password);
                    $out_edit->edit();
                    if ($out_edit->error != success)
                    {
                        System::set_message('error','Ошибка : Пароль пользователя изменить не удалось');
                        $out_edit->password = $old_password;
                        $out_edit->edit();
                        header("Location: /users/change/{$id}");
                        die();
                    }
                }


                if ($out_edit->error != success)  // А это на общий случай, если изменения в БД пошли не так.
                {
                    System::set_message('error','Ошибка : при работе с базой данных '.$out_edit->errmsg.' пожалуйста сообщите об этом администратору!');

                    $mailer = new FreakMailer();
                    $mailer->Subject = 'Проблемы при изменении данных';
                    $mailer->Body = 'Обнаружены проблемы с изменением данных у пользователя с id='.$id;
                    $mailer->AddAddress(admin_mail, 'Admin');
                    if(!$mailer->Send())
                    {
                        $userlog->action = 'Не удалось отправить оповещение о взломе на почту админа';
                        $userlog->add_action();
                    }
                    $mailer->ClearAddresses();
                    $mailer->ClearAttachments();

                    header("Location: /users/change/{$id}");
                    die();
                }


                if ($email != $old_email)
                {
                    $out_edit->checked_update(0);
                    $hash = md5(date("d-m-Y H:i:s").$email.base64_encode(date('M')).$old_username);
                    $out_edit->checkup_update($hash);

                    $mailer = new FreakMailer();

                    if (!$mailer->send_mail(
                        'Подтверждение доступа',
                        'Здравствуйте <br>
                                     Вы (или кто-то) запросили изменение контактного e-мейла к сервису мониторинга Fan-spotter и ваш емейл теперь откреплён от аккаунта.<br>
                                     Для подтверждения вашей почты пройдите пожалуйста по ссылке:<br>
                                     <a href="https://fan-spotter.ru/auth/acceptmail/'.$out_edit->id.'/'.$hash.'">https://fan-spotter.ru/auth/acceptmail/'.$out_edit->id.'/'.$hash.'</a><br>
                                     Или введите код восстановления вручную. '.$hash.'<br>
                                     Адрес ввода для ввода кода https://fan-spotter.ru/auth/acceptmail/'.$out_edit->id.'/ <br>
                                     Если вы не регистрировались на сайте fan-spotter.ru - просим проигнорировать это письмо.<br>

                                     C уважением, команда Fan-Spotter.',
                        $out_edit->email,
                        $out_edit->username
                    )
                    )
                    {
                        $userlog->action = 'Не удалось отправить письмо восстановления пароля на адрес '.$out_edit->email;
                        $userlog->add_action();
                    }


                    if (!$mailer->send_mail(
                        'Изменение контактного e-mail в системе Fan-spotter',
                        'Здравствуйте <br>
                                     Вы (или кто-то) запросили изменение контактного e-мейла к сервису мониторинга Fan-spotter и ваш емейл теперь откреплён от аккаунта.<br>
                                     Изменить e-mail вправе любой правильно авторизированный пользователь, поэтому это письмо-уведомление.
                                     Если вы не меняли свой e-mейл на сайте fan-spotter.ru - просим немедленно сообшщить об этом на почту inside@fan-spotter.ru .<br>

                                     C уважением, команда Fan-Spotter.',
                        $old_email,
                        $old_username
                    )
                    )
                    {
                        $userlog->action = 'Не удалось отправить письмо-открепление на адрес '.$out_edit->email;
                        $userlog->add_action();
                    }


                }

                if ( ($email != $old_email) && ($old_username != 'admin') ) // изменили имя или пароль или почту
                {
                    System::set_message('success','ОК : Данные изменены. Подтвердите владение измененным е-мейлом по инструкции на почте и можете пользоваться.');
                    header("Location: /auth/logout");
                    die();
                }
                elseif ($old_username == 'admin')
                {
                    System::set_message('success','ОК : Данные изменены. Сообщите самостоятельно пользователю о том, что надо пройти проверку мейла.');
                    header("Location: /users/list");
                    die();
                }

                if ( ($username != $old_username) || ($change_password == 1 && $old_password != $out_edit->password) || ($email != $old_email) ) // изменили имя или пароль или почту
                {
                    System::set_message('success','ОК : Данные изменены. Вы можете войти с новыми данными.');
                    header("Location: /auth/logout");
                    die();
                }

            }
        }

        return $this->render ("users/change_users", array ( 'out_edit' => $out_edit ) );


    }

}