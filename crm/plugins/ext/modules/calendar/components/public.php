<?php
/**
 * Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 * https://www.rukovoditel.net.ru/
 * 
 * CRM Руководитель - это свободное программное обеспечение, 
 * распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 * Государственная регистрация программы для ЭВМ: 2023664624
 * https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */
?>

<div id="calendar_public_loading" class="loading_data"></div>
<div id="calendar_public" class="fc-public"></div>

<br>

<?php
//highlighting_weekends
echo calendar::render_highlighting_weekends(CFG_PUBLIC_CALENDAR_HIGHLIGHTING_WEEKENDS);
?>


<script>

    <?php echo holidays::render_js_holidays() ?>

    var calendar_public;
    document.addEventListener('DOMContentLoaded', function()
    {
        var calendarEl = document.getElementById('calendar_public');
        calendar_public = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            initialView: '<?= calendar::getInitialView(CFG_PUBLIC_CALENDAR_DEFAULT_VIEW) ?>',            
            selectable: <?= (calendar::user_has_public_full_access() ? 'true':'false') ?>,
            editable: <?= (calendar::user_has_public_full_access() ? 'true':'false') ?>,
            dayMaxEventRows: true,
            locale: '<?= calendar::getLocale() ?>',
            firstDay: '<?php echo CFG_APP_FIRST_DAY_OF_WEEK ?>',  
            slotMinTime: '<?php echo (strlen(CFG_PUBLIC_CALENDAR_MIN_TIME) ? CFG_PUBLIC_CALENDAR_MIN_TIME : "00:00") ?>',
            slotMaxTime: '<?php echo (strlen(CFG_PUBLIC_CALENDAR_MAX_TIME) ? CFG_PUBLIC_CALENDAR_MAX_TIME : "24:00") ?>',
            slotDuration: '<?php echo (strlen(CFG_PUBLIC_CALENDAR_TIME_SLOT_DURATION) ? CFG_PUBLIC_CALENDAR_TIME_SLOT_DURATION : '00:30:00') ?>',
            selectLongPressDelay: 100,
            aspectRatio: (is_mobile ? 0.75 : 1.35),
            eventSources: [                
                {
                  url: '<?= url_for("ext/calendar/public", "action=get_events") ?>',
                  method: 'POST',
                  failure: function() {
                    alert('<?= TEXT_ERROR_LOADING_DATA ?>');
                  }
                }               
            ],
            customButtons: {
                printButton: {
                  text: '',
                  icon: 'fa fa fa-print',
                  click: function() {
                    window.print();
                  }
                },
                calendarButton: {
                    icon: 'fa fa fa-calendar',
                    click: function ()
                    {

                    }
                }
                
            },
            views: {
                year: {
                    buttonText: '<?php echo TEXT_EXT_YEAR ?>',
                    type: 'timeline',
                    duration: {year: 1},
                    slotDuration: {months: 1},
                    //slotLabelFormat: 'MMMM',
                }
            },
            headerToolbar: {
                left: 'prev,next today calendarButton',
                center: 'title',
                right: '<?php echo calendar::get_view_modes(['view_modes' => CFG_PUBLIC_CALENDAR_VIEW_MODES, 'default_view' => CFG_PUBLIC_CALENDAR_DEFAULT_VIEW]) ?>'                
            },            
            eventTimeFormat: { 
                hour: '2-digit',
                minute: '2-digit',                
                meridiem: false
            },
            select: function (e)
            {                
                open_dialog('<?php echo url_for("ext/calendar/public_form") ?>' + '&start=' + encodeURIComponent(e.startStr) + '&end=' + encodeURIComponent(e.endStr) + '&view_name=' + e.view.type)
            },
            eventResize: function (e)
            {                                      
                $.ajax({type: "POST", url: "<?php echo url_for('ext/calendar/public', 'action=resize') ?>", data: {id: e.event.id, end: e.event.end.toISOString(),view_name: e.view.type}});
            },
            eventDrop: function (e)
            {                                
                if(e.event.end)
                {
                    $.ajax({type: "POST", url: "<?php echo url_for('ext/calendar/public', 'action=drop') ?>", data: {id: e.event.id, start: e.event.start.toISOString(), end: e.event.end.toISOString(),view_name: e.view.type}});
                }
                else
                {
                    $.ajax({type: "POST", url: "<?php echo url_for('ext/calendar/public', 'action=drop') ?>", data: {id: e.event.id, start: e.event.start.toISOString()}});
                }
                
                $('.popover').remove();
            }, 
            eventClick: function (e)
            {
                if (e.event.url.length > 0)
                {
                    open_dialog(e.event.url)
                }
                
                e.jsEvent.preventDefault();
            },
            eventMouseEnter: function(e)
            {                 
                let title = e.event.title
                let description = e.event.extendedProps.description                
                                
                if ((title.length > 23 || description.length > 0))
                {
                    calendar_popover_timeout = setTimeout(function(){ 
                        $(e.el).popover({html: true, title: title, content: description, placement: 'top', container: 'body'}).popover('show');
                        let top = $(e.el).offset().top - ($('.popover').height())-5
                        let left = e.jsEvent.clientX - ($('.popover').width()/2)  
                        left = (left+300>$( window ).width() ? left-150:left)
                        $('.popover').css({top: top, left: left}) 
                    },100) 
                }                
            },
            eventMouseLeave: function(e)
            {
                $(e.el).popover('hide');  
                clearTimeout(calendar_popover_timeout)                
            },
            loading: function (bool)
            {
                $('#calendar_public_loading').toggle(bool);

                if (!bool)
                {                    
                    fc_calendar_button(this.el.getAttribute('id'))                                           
                }
            },
            datesSet: function (dateInfo) {
                for (var key in holidays)
                {
                    $("td[data-date=" + key + "], tr[data-date=" + key + "], th[data-date=" + key + "]").each(function ()
                    {
                        $(this).addClass('holiday');
                        $('.fc-daygrid-day-number, .fc-col-header-cell-cushion, .fc-list-day-text', this).attr('title', holidays[key])
                    });
                } 
            }
        });
        calendar_public.render();        
    });

    </script>
