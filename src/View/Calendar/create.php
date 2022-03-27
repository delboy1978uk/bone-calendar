<?php

use Del\Icon; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?= Icon::SHIELD ?>&nbsp;&nbsp;Calendar Admin - Add</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/calendar">Calendar</a></li>
                    <li class="breadcrumb-item active">Add Calendar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?= $msg ?>
        <div class="row justify-content-center">
            <div class="card card-primary card-outline col-md-12">
                <br>&nbsp;
                <div class="col justify-content-center">
                    <?= $form ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function(){
        var timezoneOffset = new Date().getTimezoneOffset();
        timezoneOffset = timezoneOffset == 0 ? 0 : -timezoneOffset
        timezoneOffset = timezoneOffset * 60;
        $('#timezoneOffset').val(timezoneOffset);
        console.log(timezoneOffset)
    });
</script>
