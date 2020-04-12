<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 11.04.2020
 * Time: 17:36
 */
?>
<h3>Edit task #<?=$line['id']?>:</h3>

<form action="" method="post">
    <input type="hidden" VALUE="edit" name="__action">
    <input type="hidden" value="<?=$line['description']?>" name="old_desc">
    <div class="form-row">
        <div class="form-group col-6">
            <label for="username">User name</label>
            <input type="text" class="form-control" id="username" name="username" value="<?=$line['username']?>">
        </div>
        <div class="form-group col-6">
            <label for="inputEmail">Email</label>
            <input type="email" class="form-control" id="inputEmail" name="email"  value="<?=$line['email']?>">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col">
            <label for="Textarea">Job description</label>
            <textarea class="form-control" id="Textarea" rows="3" name="job_desc"><?=$line['description']?></textarea>
        </div>
    </div>
    <div class="form-group form-check">
        <? if ($line['is_completed'] == 0 ) :?>
            <input type="checkbox" class="form-check-input" id="is_completed" name="is_completed">
        <?else :?>
            <input type="checkbox" class="form-check-input" id="is_completed" name="is_completed" checked>
        <?endif;?>
        <label class="form-check-label" for="is_completed">Is completed</label>
    </div>

    <button type="submit" class="btn btn-warning">Edit task</button>
</form>