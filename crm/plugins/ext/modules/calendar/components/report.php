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

<div id="calendar_loading<?php echo $reports['id'] ?>" class="loading_data"></div>
<div id="calendarreport<?php echo $reports['id'] ?>"></div>

<br>

<?php echo calendar::get_css($reports) ?>

<?php
//highlighting_weekends
echo calendar::render_highlighting_weekends($reports['highlighting_weekends']);


//if(calendar::user_has_reports_access($reports, 'full')):

//create default entity report for logged user
    $reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($reports['entities_id']) . "' and reports_type='calendarreport" . $reports['id'] . "' and created_by='" . $app_logged_users_id . "'");
    if(!$reports_info = db_fetch_array($reports_info_query))
    {
        $sql_data = array('name' => '',
            'entities_id' => $reports['entities_id'],
            'reports_type' => 'calendarreport' . $reports['id'],
            'in_menu' => 0,
            'in_dashboard' => 0,
            'listing_order_fields' => '',
            'created_by' => $app_logged_users_id,
        );

        db_perform('app_reports', $sql_data);
        $fiters_reports_id = db_insert_id();
    }
    else
    {
        $fiters_reports_id = $reports_info['id'];
    }

    $entity_info = db_find('app_entities', $reports['entities_id']);

    if($entity_info['parent_id'] > 0 and!isset($_GET['path']))
    {
        $add_url = url_for("reports/prepare_add_item", "redirect_to=calendarreport" . $reports['id'] . "&reports_id=" . $fiters_reports_id);
    }
    else
    {
        $add_url = url_for("items/form", "redirect_to=calendarreport" . $reports['id'] . "&path=" . (isset($_GET['path']) ? $_GET['path'] : $reports['entities_id']));
    }
    ?>

    <script>

    <?php echo holidays::render_js_holidays() ?>

    var calendarreport<?= $reports['id'] ?>;
    document.addEventListener('DOMContentLoaded', function()
    {
        var calendarEl = document.getElementById('calendarreport<?= $reports['id'] ?>');
        calendarreport<?= $reports['id'] ?> = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            initialView: '<?= calendar::getInitialView($reports['default_view']) ?>',            
            selectable: <?= ((calendar::user_has_reports_access($reports, 'full') and $reports['entities_id']!=1) ? 'true':'false') ?>,
            editable: <?= ((calendar::user_has_reports_access($reports, 'full') and $reports['entities_id']!=1) ? 'true':'false') ?>,
            dayMaxEventRows: true,
            timeZone: '<?= CFG_APP_TIMEZONE ?>',
            locale: '<?= calendar::getLocale() ?>',
            firstDay: '<?php echo CFG_APP_FIRST_DAY_OF_WEEK ?>',  
            slotMinTime: '<?php echo (strlen($reports['min_time']??'') ? $reports['min_time'] : "00:00") ?>',
            slotMaxTime: '<?php echo (strlen($reports['max_time']??'') ? $reports['max_time'] : "24:00") ?>',
            slotDuration: '<?php echo (strlen($reports['time_slot_duration']??'') ? $reports['time_slot_duration'] : '00:30:00') ?>',
            selectLongPressDelay: 100,
            aspectRatio: (is_mobile ? 0.75 : 1.35),
            eventSources: [                
                {
                  url: '<?= url_for("ext/calendar/report", "action=get_events&id=" . $reports["id"] . (isset($_GET["path"]) ? "&path=" . $_GET["path"] : "")) ?>',
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
                right: '<?php echo calendar::get_view_modes($reports) ?>'                
            },            
            eventTimeFormat: { 
                hour: '2-digit',
                minute: '2-digit',                
                meridiem: false
            },
            select: function (e)
            {                
                open_dialog('<?php echo $add_url ?>' + '&start=' + encodeURIComponent(e.startStr) + '&end=' + encodeURIComponent(e.endStr) + '&view_name=' + e.view.type)
            },
            eventResize: function (e)
            {                                      
                $.ajax({type: "POST", url: "<?php echo url_for('ext/calendar/report', 'action=resize&id=' . $reports['id']) ?>", data: {id: e.event.id, end: e.event.end.toISOString(),view_name: e.view.type}});
            },
            eventDrop: function (e)
            {                                
                if(e.event.end)
                {
                    $.ajax({type: "POST", url: "<?php echo url_for('ext/calendar/report', 'action=drop&id=' . $reports['id']) ?>", data: {id: e.event.id, start: e.event.start.toISOString(), end: e.event.end.toISOString(),view_name: e.view.type}});
                }
                else
                {
                    $.ajax({type: "POST", url: "<?php echo url_for('ext/calendar/report', 'action=drop&id=' . $reports['id']) ?>", data: {id: e.event.id, start: e.event.start.toISOString()}});
                }
                
                $('.popover').remove();
            }, 
            eventClick: function (e)
            {                
                $(e.el).attr('target', '_new')                
            },
            eventMouseEnter: function(e)
            {                 
                let title = e.event.title
                let description = e.event.extendedProps.description                
                                
                if ((title.length > 23 || description.length > 0) )
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
                $('#calendar_loading<?= $reports['id'] ?>').toggle(bool);

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
        calendarreport<?= $reports['id'] ?>.render();        
    });

    </script>