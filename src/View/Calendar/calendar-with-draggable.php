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
                            <h3 class="card-title">Create Event</h3>
                        </div>
                        <div class="card-body">
                            <div id="choose-day">
                                <p><i class="fa fa-calendar"></i> Select a day for your event</p>
                            </div>
                            <div id="add-details" class="hide">
                                <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                                    <ul class="fc-color-picker" id="color-chooser">
                                        <li><a class="text-primary" href="#" data-content="blue"><i class="fa fa-square"></i></a></li>
                                        <li><a class="text-warning" href="#" data-content="orange"><i class="fa fa-square"></i></a></li>
                                        <li><a class="text-success" href="#" data-content="green"><i class="fa fa-square"></i></a></li>
                                        <li><a class="text-danger" href="#" data-content="red"><i class="fa fa-square"></i></a></li>
                                        <li><a class="text-muted" href="#" data-content="grey"><i class="fa fa-square"></i></a></li>
                                    </ul>
                                </div>
                                <div id="nocolor" class="text-danger hide">Please select a color.</div>
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

                            <div id="external-events" class="hide">
                                <p><i class="fa fa-arrows-h"></i> Drag the appointment</p>
                                <div id="draggable"  class="appointment-div external-event ui-draggable ui-draggable-handle" style="position: relative;">
                                    <span id="event-name"></span>
                                </div>
                            </div>

                            <div id="confirm" class="hide">
                                <button id="confirm-appointment"  class="btn btn--primary">Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div id="calendar"></div>
            </div>
        </div>

    </div>
</section>
<style>
    .rotate {
        transform: rotate(30deg);
    }
</style>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {

        let draggableEl = document.getElementById('external-events');
        let calendarEl = document.getElementById('calendar');
        var eventDragged = false;
        var eventToInsert = {};

        let calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap',
            droppable: true,
            editable: false,
            eventOverlap: false,
            selectable: false,
            allDaySlot: false,
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
                displayConfirm()
            },
            eventResize: function (info) {
                eventToInsert = info.event;
                var start = info.event.start;
                var end = info.event.end;
                var duration = (((end - start) / 1000) / 60) / 60;
                setDuration(duration);
            },
            dateClick: function(info) {
                calendar.changeView( 'timeGridDay', info.date );
            },
            datesSet: function( info ) {
                if (info.end - info.start > (60 * 60 * 24 * 1000)) {
                    $('#choose-day').removeClass('hide');
                    $('#add-details').addClass('hide');
                } else {
                    $('#choose-day').addClass('hide');
                    $('#add-details').removeClass('hide');
                }
            }
        });

        calendar.render();

        new FullCalendar.Draggable(draggableEl);

        $('#confirm-appointment').click(function() {
            $(this).html('<i class="fa fa-spin"></i>');
            insertEvent()
        });

        function insertEvent() {
            var calendarData = {
                startDate: eventToInsert.start.toISOString(),
                endDate: eventToInsert.end,
                event: eventToInsert.title,
                link: eventToInsert.url,
                owner: eventToInsert.extendedProps.owner,
                color: eventToInsert.color
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
                    alert('success')
                },
                error: function(jqXHR,status,error){
                    console.log(jqXHR);
                    console.log(status);
                    console.log(error);
                }
            });
        }

        function isValid()  {
            let newEvent = $('#new-event');
            let color = $('#color-chooser li a i.rotate').parent().data('content');

            let invalid = $('#invalid');
            let noColor = $('#nocolor');
            let name = newEvent.val();
            var valid = false

            if (name.length < 2) {
                newEvent.addClass('border-danger');
                invalid.removeClass('hide');
            } else {
                newEvent.removeClass('border-danger');
                invalid.addClass('hide');
            }

            if (!color) {
                noColor.removeClass('hide');
            } else {
                noColor.addClass('hide');
            }

            return name.length > 2 && color;
        }

        $('#add-new-event').click(function (e) {
            if (isValid()) {
                let eventName = $('#new-event').val();
                let eventUrl = $('#event-url').val();
                let color = $('#color-chooser li a i.rotate').parent().data('content');
                let backgroundEvent = $('#bg-event').prop('checked');
                createDraggable(eventName, color, backgroundEvent)
                displayDraggable()
            }
        });

        $('#color-chooser li a i').click(function () {
            $('#color-chooser li a i').removeClass('rotate')
            $(this).addClass('rotate')
        });

        function createDraggable(eventName, eventUrl, color, backgroundEWvent) {
            var draggables = document.getElementById('draggable');
            new FullCalendar.Draggable(draggables, {
                itemSelector: '.appointment-div',
                eventData: function(eventEl) {
                    return {
                        title: eventName,
                        owner: <?= $user->getId() ?>,
                        editable: true,
                        color: color,
                        duration: "01:00",
                        url: eventUrl
                    };
                }
            });
            $('#event-name').html(eventName)
            $('#draggable').addClass(eventName)
        }

        function displayDraggable() {
            $('#add-details').hide()
            $('#external-events').show()
        }

        function displayConfirm() {
            $('#external-events').hide()
            $('#confirm').show()
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
                case 0.8333333333333334:
                    duration = 50;
                    hrs = 'minutes'
                    break;
                case 1:
                    hrs = 'hr'
                    break;
            }

            $('#duration').html(duration);
            $('#hrs').html(hrs);
        }
    });
</script>
