<?php use Del\Icon; ?>
<link rel="stylesheet" href="/bone-calendar/fullcalendar/main.min.css">
<script src='/bone-calendar/fullcalendar/main.js'></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?= \Del\Icon::CALENDAR ?>&nbsp;&nbsp;Calendar</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item active">Calendar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="sticky-top mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Draggable Events</h4>
                        </div>
                        <div class="card-body">
                            <div id="choose-type" data-type="">
                                <p class="external-event bg-info"><?= Icon::DATABASE ?> Appointment type</p>
                                <div class="appointment-types">
                                    <span class="mt20">&nbsp;</span>
                                    <span title="New Patient"  data-description="New Patient" data-value="primary" class="tt hover-finger badge active-primary badge-secondary mt20">New</span>
                                    <span title="IV Sedation"  data-description="IV Sedation" data-value="indigo" class="tt hover-finger badge active-indigo badge-secondary mt20">IV</span>
                                    <span title="LA Treatment" data-description="LA Treatment" data-value="orange" class="tt hover-finger badge active-orange badge-secondary mt20">LA</span>
                                    <span title="Biopsy" data-description="Biopsy" data-value="teal" class="tt hover-finger badge active-teal badge-secondary mt20">Biopsy</span>
                                    <span title="Review" data-description="Review" data-value="info"  class="tt hover-finger badge active-info badge-secondary mt20">Review</span>
                                </div>
                            </div>
                            <p class="text-white corners" id="type"></p>
                            <div id="choose-day" class="hide">
                                <p class="external-event bg-info"><?= Icon::CALENDAR ?> Select a day</p>
                            </div>
                            <div id="choose-length" class="hide text-center">
                                <p class="external-event bg-info">
                                    <?= Icon::ARROWS_V ?> Drag the length you need</p>
                                <br>
                                <p class="lead">
                                    <span class="text-muted"><?= Icon::CALENDAR_O ?></span> <strong><span id="appointment-date"></span></strong><br>
                                    <span class="text-muted"><?= Icon::CLOCK_O ?></span> <strong><span id="appointment-time"></span></strong>
                                    for <span id="duration">1</span> <span id="hrs">hr</span>
                                </p>
                                <br>
                                <button id="confirm-appointment" class="btn btn-success btn-block">
                                    <?= Icon::CHECK ?> Confirm Appointment
                                </button>
                                <a id="confirm-appointment" href="" class="btn btn-danger btn-block">
                                    <?= Icon::RECYCLE ?> Start Again
                                </a>
                            </div>
                            <div id="external-events" class="hide">
                                <p class="external-event bg-info"><?= Icon::ARROWS_H ?> Drag the appointment</p>
                                <div id="draggable"  class="drag-div external-event ui-draggable ui-draggable-handle" style="position: relative;">
                                    <span id="patient">xxx</span>
                                    <span class="pull-right">1 hr</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Create Event</h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                                <ul class="fc-color-picker" id="color-chooser">
                                    <li><a class="text-primary" href="#"><i class="fas fa-square"></i></a></li>
                                    <li><a class="text-warning" href="#"><i class="fas fa-square"></i></a></li>
                                    <li><a class="text-success" href="#"><i class="fas fa-square"></i></a></li>
                                    <li><a class="text-danger" href="#"><i class="fas fa-square"></i></a></li>
                                    <li><a class="text-muted" href="#"><i class="fas fa-square"></i></a></li>
                                </ul>
                            </div>
                            <div class="input-group">
                                <input id="new-event" type="text" class="form-control" placeholder="Event Title">

                                <div class="input-group-append">
                                    <button id="add-new-event" type="button" class="btn btn-primary">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div id="calendar"></div>
            </div>
        </div>

    </div>
</section>

<script type="text/javascript">
    $(document).ready(function() {
        var Calendar = FullCalendar.Calendar;
        var Draggable = FullCalendar.Draggable;

        var containerEl = document.getElementById('external-events');
        var calendarEl = document.getElementById('calendar');
        var checkbox = document.getElementById('drop-remove');

        // initialize the external events
        // -----------------------------------------------------------------

        new Draggable(containerEl, {
            itemSelector: '.fc-event',
            eventData: function(eventEl) {
                return {
                    title: eventEl.innerText
                };
            }
        });

        // initialize the calendar
        // -----------------------------------------------------------------

        var calendar = new Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar
            drop: function(info) {
                // is the "remove after drop" checkbox checked?
                if (checkbox.checked) {
                    // if so, remove the element from the "Draggable Events" list
                    info.draggedEl.parentNode.removeChild(info.draggedEl);
                }
            }
        });

        calendar.render();
    });
</script>
