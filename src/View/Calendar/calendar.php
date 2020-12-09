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


<script>

    function updateEvent(event)
    {
        calendarData = {
          id: event.extendedProps.calendarID,
          event: event.title,
          link: event.url,
          owner: event.extendedProps.owner,
          startDate: event.start,
          endDate: event.end,
        };
        $.ajax({
            url: '/api/calendar/' + calendarData.id,
            type: 'PUT',
            data: JSON.stringify(calendarData),
            contentType: 'application/json',
            mimeType: 'multipart/form-data',
            cache: false,
            processData: false,
            success: function(data, status, jqXHR){
                alert('Hooray! All is well.');
                console.log(data);
                console.log(status);
                console.log(jqXHR);

            },
            error: function(jqXHR,status,error){
                // Hopefully we should never reach here
                console.log(jqXHR);
                console.log(status);
                console.log(error);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {

        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap',
            editable: true,
            eventOverlap: false,
            selectable: true,
            headerToolbar: {
                start: 'today prev,next',
                center: 'title',
                end: 'dayGridMonth,timeGridWeek,timeGridDay',
            },
            events: '/api/calendar/events',
            initialView: 'timeGridWeek',
            slotMinTime: '08:00',
            eventResize: function (info) {
                updateEvent(info.event);
            },
            eventDrop: function( info ) {
                updateEvent(info.event);
            }
        });
        calendar.render();
    });

</script>
<section class="content">
    <div class="container">
        <div id="calendar"></div>
    </div>
</section>
