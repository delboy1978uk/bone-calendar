<link rel="stylesheet" href="/bone-calendar/fullcalendar/main.min.css">
<link rel="stylesheet" href="/bone-calendar/fullcalendar-daygrid/main.min.css">
<link rel="stylesheet" href="/bone-calendar/fullcalendar-timegrid/main.min.css">
<link rel="stylesheet" href="/bone-calendar/fullcalendar-bootstrap/main.min.css">
<script src='/bone-calendar/fullcalendar/main.js'></script>
<script src='/bone-calendar/fullcalendar-bootstrap/main.min.js'></script>
<script src='/bone-calendar/fullcalendar-daygrid/main.min.js'></script>
<script src='/bone-calendar/fullcalendar-timegrid/main.min.js'></script>

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


<script>

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'dayGrid', 'timeGrid', 'bootstrap'],
            initialView: 'dayGridMonth'
        });
        calendar.render();
    });

</script>
<section class="content">
    <div class="container-fluid">
        <div id="calendar"></div>
    </div>
</section>
