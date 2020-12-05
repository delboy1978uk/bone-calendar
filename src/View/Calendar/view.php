<?php
use Del\Icon;
/** @var \Bone\Calendar\Entity\Calendar $calendar */
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?= Icon::SHIELD ?>&nbsp;&nbsp;Calendar Admin - View</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/calendar">Calendar</a></li>
                    <li class="breadcrumb-item active">View Event</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="card card-primary card-outline col-md-12">
                <div class="card-body p-10">
                    <div class="mailbox-read-info">
                        <h2><?= $calendar->getEvent() ?></h2>
                    </div>
                    <div class="mailbox-read-message">
                        <p><?= $calendar->getStartDate()->format('d M Y H:i') . ' - ' . $calendar->getEndDate()->format('H:i') ?></p>
                        <p><?= $calendar->getLink() ? '<a href="' . $calendar->getLink() . '">' . $calendar->getLink() . '</a>' : '' ?></p>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="float-right">
                        <a href="/admin/calendar" class="btn btn-default"><i class="fa fa-backward"></i> Back</a>
                        <a href="/admin/calendar/edit/<?= $calendar->getId() ?>" class="btn btn-primary"><?= Icon::EDIT ;?> Edit</a>
                    </div>
                    <a href="/admin/calendar/delete/<?= $calendar->getId() ?>" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a>
                </div>
            </div>
        </div>
    </div>
</section>
