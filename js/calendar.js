var calendar = null;
var mode = 0;

// Mark element when hovered (needed for deleting events by dragging)
$(document).on({
    mouseenter: function(evt) {
        $(evt.target).data('hovering', true);
    },
    mouseleave: function(evt) {
        $(evt.target).data('hovering', false);
    }
}, "*");

jQuery.expr[":"].hovering = function(elem) {
    return $(elem).data('hovering');
};

$(function () {
    /* initialize the external events
     -----------------------------------------------------------------*/
    function init_events(ele) {
        ele.each(function () {

            // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
            // it doesn't need to have a start or end
            var eventObject = {
                title: $.trim($(this).text()) // use the element's text as the event title
            };

            // store the Event Object in the DOM element so we can get to it later
            $(this).data('eventObject', eventObject);

            // make the event draggable using jQuery UI
            $(this).draggable({
                zIndex        : 1070,
                revert        : true, // will cause the event to go back to its
                revertDuration: 0  //  original position after the drag
            });

        });
    }

    init_events($('#external-events div.external-event'));

    /* initialize the calendar
     -----------------------------------------------------------------*/
    //Date for the calendar events (dummy data)
    var date = new Date();
    var d    = date.getDate(),
        m    = date.getMonth(),
        y    = date.getFullYear();
    calendar = $('#calendar').fullCalendar({
        header    : {
            left  : 'prev,next today',
            center: 'title',
            right : 'month,agendaWeek,agendaDay'
        },
        buttonText: {
            today: 'Dziś',
            month: 'Miesiąc',
            week : 'Tydzień',
            day  : 'Dzień'
        },
        //Random default events
        /*events    : [
            {
                title          : 'All Day Event',
                start          : new Date(y, m, 1),
                backgroundColor: '#f56954', //red
                borderColor    : '#f56954' //red
            },
            {
                title          : 'Long Event',
                start          : new Date(y, m, d - 5),
                end            : new Date(y, m, d - 2),
                backgroundColor: '#f39c12', //yellow
                borderColor    : '#f39c12' //yellow
            },
            {
                title          : 'Meeting',
                start          : new Date(y, m, d, 10, 30),
                allDay         : false,
                backgroundColor: '#0073b7', //Blue
                borderColor    : '#0073b7' //Blue
            },
            {
                title          : 'Lunch',
                start          : new Date(y, m, d, 12, 0),
                end            : new Date(y, m, d, 14, 0),
                allDay         : false,
                backgroundColor: '#00c0ef', //Info (aqua)
                borderColor    : '#00c0ef' //Info (aqua)
            },
            {
                title          : 'Birthday Party',
                start          : new Date(y, m, d + 1, 19, 0),
                end            : new Date(y, m, d + 1, 22, 30),
                allDay         : false,
                backgroundColor: '#00a65a', //Success (green)
                borderColor    : '#00a65a' //Success (green)
            },
            {
                title          : 'Click for Google',
                start          : new Date(y, m, 28),
                end            : new Date(y, m, 29),
                url            : 'http://google.com/',
                backgroundColor: '#3c8dbc', //Primary (light-blue)
                borderColor    : '#3c8dbc' //Primary (light-blue)
            }
        ],*/
        editable  : true, // (variable) only leader is allowed to edit or drop, even when it's replaced there is server-side validation
        eventDrop: function(event, delta, revertFunc) {

            var id = event.id;
            var moment_start = moment(event.start).format("DD.MM.YYYY HH:mm");
            var moment_stop = moment(event.end).format("DD.MM.YYYY HH:mm");
            updateEvent(id, moment_start, moment_stop);
        },
        dragRevertDuration: 0,
        eventDragStop: function(event, jsEvent, ui, view) {
            var trashEl = jQuery("#dropzone");
            var ofs = trashEl.offset();

            var x1 = ofs.left;
            var x2 = ofs.left + trashEl.outerWidth(true);
            var y1 = ofs.top;
            var y2 = ofs.top + trashEl.outerHeight(true);

            if (jsEvent.pageX >= x1 && jsEvent.pageX<= x2 && jsEvent.pageY >= y1 && jsEvent.pageY <= y2)
            {
                $.get("/ajax/CalendarDeleteEvent.php?eventid=" + event.id).done(function(data) {
                    var obj = jQuery.parseJSON(JSON.stringify(data));
                    if (obj.code === -1)
                    {
                        modalError("Błąd", obj.msg);
                    }
                    else if (obj.code === 0)
                    {
                        modalWarning("Uwaga", obj.msg);
                    }
                    else if (obj.code === 1)
                    {
                        $('#calendar').fullCalendar('removeEvents', event.id);
                        modalSuccess("Powodzenie", "Usunięto wydarzenie: " + event.title);
                    }
                });
            }
        },
        droppable : (window.localStorage.isLeader === "1"), // this allows things to be dropped onto the calendar !!!
        drop      : function (date, allDay) { // this function is called when something is dropped

            // retrieve the dropped element's stored Event Object
            var originalEventObject = $(this).data('eventObject');

            // we need to copy it, so that multiple events don't have a reference to the same object
            var copiedEventObject = $.extend({}, originalEventObject);

            // assign it the date that was reported
            copiedEventObject.start           = date;
            copiedEventObject.allDay          = allDay;
            copiedEventObject.backgroundColor = $(this).css('background-color');
            copiedEventObject.borderColor     = $(this).css('border-color');

            // render the event on the calendar
            // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
            $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

            // is the "remove after drop" checkbox checked?
            if ($('#drop-remove').is(':checked')) {
                // if so, remove the element from the "Draggable Events" list
                $(this).remove();
            }

        }
    });

    /* ADDING EVENTS */
    var currColor = '#3c8dbc'; //Red by default
    //Color chooser button
    var colorChooser = $('#color-chooser-btn');
    $('#color-chooser > li > a').click(function (e) {
        e.preventDefault();
        //Save color
        currColor = $(this).css('color');
        //Add color effect to button
        $('#add-new-event').css({ 'background-color': currColor, 'border-color': currColor })
    });
    $('#add-new-event').click(function (e) {
        e.preventDefault();
        //Get value and make sure it is not null
        var val = $('#new-event').val();
        if (val.length === 0) {
            return;
        }

        //Create events
        var event = $('<div />');
        event.css({
            'background-color': currColor,
            'border-color'    : currColor,
            'color'           : '#fff'
        }).addClass('external-event');
        event.html(val);
        $('#external-events').prepend(event);

        //Add draggable funtionality
        init_events(event);

        //Remove event from text input
        $('#new-event').val('');
    });
});

function updateEvent(id, start, end)
{
    var start_prepared = encodeURI(start.toString());
    var end_prepared = encodeURI(end.toString());
    $.get("/ajax/CalendarUpdateEvent.php?teamid=" + getUrlParameter("teamid") + "&eventid=" + id + "&startdate=" + start_prepared + "&enddate=" + end_prepared).done(function(data) {
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code === -1)
        {
            modalError("Błąd", obj.msg);
        }
        else if (obj.code === 0)
        {
            modalWarning("Uwaga", obj.msg);
        }
        else if (obj.code !== 1)
        {
            modalError("Błąd", "Nieznana odpowiedź serwera: " + obj.msg);
        }
    });
}

function addEvent(id, title, startdatetime, enddatetime, priority)
{
    var color;
    switch(priority)
    {
        case "low":
            color = "#7CFC00";
            break;

        case "medium":
            color = "#FFD700";
            break;

        case "high":
            color = "#B22222";
            break;

        default:
            return false;
    }

    calendar.fullCalendar('renderEvent',
    {
        id: id,
        title: title,
        start: moment(startdatetime, "DD.MM.YYYY HH.mm"),
        end: moment(enddatetime, "DD.MM.YYYY HH.mm"),
        borderColor: "#000000",
        textColor: "#000000",
        color: color
    }, true);

    return true;
}