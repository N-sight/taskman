<?php
/**
 * Created by PhpStorm.
 * User: Вячеслав
 * Date: 11.04.2020
 * Time: 17:36
 */
?>
<h3>New task:</h3>

<form action="" method="post">
    <input type="hidden" VALUE="add" name="__action">
    <div class="form-row">
        <div class="form-group col-6">
            <label for="username">User name</label>
            <input type="text" class="form-control" id="username" name="username">
        </div>
        <div class="form-group col-6">
            <label for="inputEmail">Email</label>
            <input type="email" class="form-control" id="inputEmail" name="email">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col">
            <label for="Textarea">Job description</label>
            <textarea class="form-control" id="Textarea" rows="3" name="job_desc"></textarea>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Add task</button>
</form>