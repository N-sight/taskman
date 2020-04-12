<?php
$title = isset($title) ? $title : TITLE_CONST;
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" >
    <title><?=$title;?></title>
  </head>
  <body>
         <div  class="container-fluid p-30">
            <?
            $success = System::get_message('success');
            $error = System::get_message('error');
            $warning = System::get_message('warning');
            if ($success !== NULL) : ?>
                <div class = "alert alert-success"><?=$success?></div>
            <? endif; ?>

            <?  if ($error !== NULL) : ?>
                <div class = "alert alert-danger"><?=$error?></div>
            <? endif; ?>

            <?  if ($warning !== NULL) : ?>
                <div class = "alert alert-warning"><?=$warning?></div>
            <? endif; ?>
        </div>

        <div  class="container-fluid">
            <div class="row m-3">
                <div class="col">
                    <a class="btn btn-primary btn-lg" href="/task/add" role="button"> + Task </a>
                </div>
                <div class="col-6">
                    <a class="text-center" href="/task/list"> <h2 style="font-weight: bold;">Task manager. Admin.</h2> </a>
                </div>
                <div class="col-4 float-right">
                    <a class="btn btn-warning" style="display: block; margin: 0 20px 0 0;" href="/auth/logout">Logout</a>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="row" style="display: block; padding: 0 20px;">
                    <?=$content;?>
                </div>
            </div> <!-- container -->

        </div> <!-- content -->

    <script src="/js/bootstrap.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </body>
</html>