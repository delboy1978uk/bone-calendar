<?php use Del\Icon; ?>
<style type="text/css">
    span.hover-finger:hover {
        margin-top: 20px;
        cursor: pointer;
        min-height: 30px;
        line-height: 30px;
        font-size: 1em;
    }
    .corners{
        border-radius: 0.25rem;
        padding: 5px 10px;
        font-weight: 700;
    }
</style>
<link rel="stylesheet" href="/bone-calendar/fullcalendar/main.min.css">
<script src='/bone-calendar/fullcalendar/main.js'></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?= Icon::CALENDAR ?>&nbsp;&nbsp;Create Event</h1>
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
                            <h4 id="step" class="card-title">Choose a colour</h4>
                        </div>
                        <div class="card-body">
                            <div id="choose-type" data-type="">
                                <div class="appointment-types">
                                    <span class="mt20">&nbsp;</span>
                                    <span title="Blue"  data-description="Blue" data-value="primary" class="tt hover-finger badge active-primary badge-primary mt20">Blue</span>
                                    <span title="Red"  data-description="Red" data-value="danger" class="tt hover-finger badge active-danger badge-danger mt20">Red</span>
                                    <span title="Orange" data-description="Orange" data-value="warning" class="tt hover-finger badge active-warning badge-warning mt20">Orange</span>
                                    <span title="Green" data-description="Green" data-value="success" class="tt hover-finger badge active-success badge-success mt20">Green</span>
                                    <span title="Teal" data-description="Teal" data-value="info"  class="tt hover-finger badge active-info badge-info mt20">Teal</span>
                                </div>
                            </div>
                            <div id="choose-day" class="hide">
                                <p><?= Icon::CALENDAR ?> Select a day</p>
                            </div>
                            <div id="add-details" class="hide">
                                <div class="input-group">
                                    <input id="new-event" type="text" class="form-control" placeholder="Event Title">

                                    <div class="input-group-append">
                                        <button id="add-new-event" type="button" class="btn btn-primary">Add</button>
                                    </div>
                                    <div id="invalid" class="text-danger hide">Please enter 2 or more characters.</div>
                                </div>
                                <input type="text" id="event-url" class="form-control" placeholder="(optional) Event URL..."/>
                                <div class="btn-group text-muted">
                                    <input id="bg-event" type="checkbox"/>&nbsp;Background event
                                </div>
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
                                <a id="start-again" href="" class="btn btn-danger btn-block">
                                    <?= Icon::RECYCLE ?> Start Again
                                </a>
                            </div>
                            <div id="external-events" class="hide">
                                <div id="draggable"  class="appointment-div external-event ui-draggable ui-draggable-handle" style="position: relative;">
                                    <span id="Event-name">EVENT NAME</span>
                                    <span class="pull-right">1 hr</span>
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
<div id="appointment-added" data-backdrop="static" data-keyboard="false" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Created</h5>
            </div>
            <div class="modal-body">
                <p>The appointment has been set to the following:</p>
                <p class="lead">
                    <?= Icon::CLOCK_O ?> <span id="new-appt-date"></span>
                </p>
            </div>
            <div class="modal-footer">
                <button id="close-button" type="button" class="btn btn-success" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>


    document.addEventListener('DOMContentLoaded', function() {

        var eventToInsert = {};

        $('#confirm-appointment').click(function(){
            $(this).html('<?= Icon::custom(Icon::SPINNER, 'fa-spin') ?>');
            let bgColor = $('#choose-type').data('type')
            var calendarData = {
                startDate: eventToInsert.start.toISOString(),
                endDate: eventToInsert.end,
                event: eventToInsert.title,
                link: eventToInsert.url,
                owner: eventToInsert.extendedProps.owner,
                color: bgColor
            };

            $.ajax({
                url: '/api/calendar',
                type: 'POST',
                data: JSON.stringify(calendarData),
                contentType: 'application/json',
                mimeType: 'multipart/form-data',
                cache: false,
                processData: false,
                success: function(data, status, jqXHR){
                    let appt = JSON.parse(data);
                    var apptDate = new Date(appt.startDate)
                    $('#confirm-appointment').html('<?= Icon::CHECK ?>');
                    $('#new-appt-date').html(apptDate.toLocaleString());
                    $('#appointment-added').modal();
                },
                error: function(jqXHR,status,error){
                    console.log(jqXHR);
                    console.log(status);
                    console.log(error);
                }
            });
        });

        // appointment type
        $('span.hover-finger').click(function(e) {
            let bgClass = $(this).data('value');
            let description = $(this).data('description');
            let choose = $('#choose-type');
            choose.data('type', bgClass);
            choose.addClass('hide');
            $('#step').html('Select a day')
            $('#choose-day').removeClass('hide');
            $('#calendar').removeClass('hide');
            $('#draggable').addClass('bg-' + bgClass);
            $('#draggable').data('color', bgClass);
            eventToInsert.color = bgClass;
        });

        $('#add-new-event').click(function (e) {
            if (isValid()) {
                eventToInsert.title = $('#new-event').val();
                eventToInsert.url = $('#event-url').val();
                eventToInsert.allDay = false;
                if ($('#bg-event').prop('checked')) {
                    eventToInsert.display = 'background';
                    eventToInsert.allDay = true;
                }
                createDraggable()
                displayDraggable()
            }
        });

        function isValid()  {
            let newEvent = $('#new-event');
            let invalid = $('#invalid');
            let name = newEvent.val();
            var valid = false

            if (name.length < 2) {
                newEvent.addClass('border-danger');
                invalid.removeClass('hide');
            } else {
                newEvent.removeClass('border-danger');
                invalid.addClass('hide');
            }

            return name.length > 2;
        }

        function createDraggable() {
            eventToInsert.owner = <?= $user->getId() ?>;
            eventToInsert.editable = false;
            if (eventToInsert.allDay !== true) {
                eventToInsert.duration = "02:00";
                eventToInsert.editable = true;
            }
            console.log(eventToInsert)
            var draggables = document.getElementById('draggable');
            new FullCalendar.Draggable(draggables, {
                itemSelector: '.appointment-div',
                eventData: function(eventEl) {
                    return eventToInsert;
                }
            });
            $('#draggable').html(eventToInsert.title)
            $('#draggable').addClass(eventToInsert.title)
        }

        function displayDraggable() {
            $('#add-details').hide()
            $('#external-events').show()
        }

        function getDayName(date)
        {
            var daynum = date.getDay();
            var days = {
                0: 'Sun',
                1: 'Mon',
                2: 'Tue',
                3: 'Wed',
                4: 'Thu',
                5: 'Fri',
                6: 'Sat'
            };

            return days[daynum];
        }

        function formatDate(date)
        {
            return getDayName(date) + ' ' + date.getDate() + '/' + (date.getMonth() + 1) + '/' + date.getFullYear();
        }

        function formatTime(date)
        {
            var minutes = date.getMinutes();
            var hours = date.getHours();

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            if (hours < 10) {
                hours = '0' + hours;
            }

            return hours  + ':' + minutes;
        }

        function setDuration(duration)
        {
            var hrs = 'hrs';

            switch (duration) {
                case 0.16666666666666666:
                    duration = 10;
                    hrs = 'minutes'
                    break;
                case 0.25:
                    duration = 15;
                    hrs = 'minutes'
                    break;
                case 0.3333333333333333:
                    duration = 20;
                    hrs = 'minutes'
                    break;
                case 0.5:
                    duration = 30;
                    hrs = 'minutes'
                    break;
                case 0.6666666666666666:
                    duration = 40;
                    hrs = 'minutes'
                    break;
                case 0.75:
                    duration = 45;
                    hrs = 'minutes'
                    break;
                case 0.8333333333333334:
                    duration = 50;
                    hrs = 'minutes'
                    break;
                case 1:
                    hrs = 'hr'
                    break;
                default:
                    duration = Math.round(duration * 100) / 100
            }

            $('#duration').html(duration);
            $('#hrs').html(hrs);
        }

        var calendarEl = document.getElementById('calendar');
        var Draggable = FullCalendar.Draggable;
        var eventDragged = false;
        var draggables = document.getElementById('draggable');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap',
            droppable: true,
            editable: false,
            eventOverlap: false,
            selectable: false,
            allDaySlot: true,
            headerToolbar: {
                start: 'today prev,next',
                center: 'title',
                end: 'dayGridMonth,timeGridWeek,timeGridDay',
            },
            events: '/api/calendar/events',
            initialView: 'dayGridMonth',
            slotMinTime: '08:00',
            slotDuration: '00:10:00',
            businessHours: {
                daysOfWeek: [ 1, 2, 3, 4, 5 ],
                startTime: '09:00',
                endTime: '17:00'
            },
            eventReceive: function(info) {
                eventDragged = true;
                eventToInsert = info.event;
                var date = formatDate(info.event.start);
                var time = formatTime(info.event.start);
                $('#appointment-date').html(date);
                $('#appointment-time').html(time);
                $('#choose-day').addClass('hide');
                $('#draggable').addClass('hide');
                $('#choose-length').removeClass('hide');
            },
            eventResize: function (info) {
                eventToInsert = info.event;
                var start = info.event.start;
                var end = info.event.end;
                var duration = (((end - start) / 1000) / 60) / 60;
                setDuration(duration);
            },
            eventDrop: function( info ) {
                eventToInsert = info.event;
                $('#appointment-time').html(formatTime(info.event.start));
            },
            dateClick: function(info) {
                if($('#choose-type').data('type').length > 0) {
                    calendar.changeView( 'timeGridDay', info.date );
                }
            },
            datesSet: function( info ) {
                if (info.end - info.start > (60 * 60 * 24 * 1000)) {
                    if ($('#choose-type').data('type')) {
                        $('#choose-day').removeClass('hide');
                    }
                    $('#step').html('Choose a day');
                    $('#add-details').addClass('hide');
                } else {
                    $('#choose-day').addClass('hide');

                    if (eventDragged === false) {
                        $('#step').html('Add details');
                        $('#add-details').removeClass('hide');
                    }
                }
            }
        });
        calendar.render();
    });

    $('#close-button').click(function(){
        window.location.href = $('#start-again').prop('href');
    });

</script>
