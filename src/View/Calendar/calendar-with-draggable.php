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
                            <div id="external-events">
                                <div class="external-event bg-success ui-draggable ui-draggable-handle" style="position: relative;">Lunch</div>
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
                            <div class="btn-group text-muted">
                                <input id="bg-event" type="checkbox"/>&nbsp;Background event
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div id="calendar"></div>
        </div>

    </div>
</section>
<style>
    .rotate {
        transform: rotate(30deg);
    }
</style>
<script type="text/javascript">
    $(document).ready(function(){

        let draggableEl = document.getElementById('external-events');
        let calendarEl = document.getElementById('calendar');

        let calendar = new Calendar(calendarEl, {
            droppable: true
        });

        calendar.render();

        new Draggable(draggableEl);

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
                let color = $('#color-chooser li a i.rotate').parent().data('content');
                let backgroundEvent =
                alert ('now check for background checkbox')
            }
        });

        $('#color-chooser li a i').click(function () {
            $('#color-chooser li a i').removeClass('rotate')
            $(this).addClass('rotate')
        });
    });
</script>
