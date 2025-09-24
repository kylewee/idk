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

<?php echo pivot_calendars::render_legend($reports) ?>

<div id="pivot_calendars_loading<?php echo $reports['id'] ?>" class="loading_data"></div>
<div id="pivot_calendars<?php echo $reports['id'] ?>"></div>

<br>

<?php
//highlighting_weekends
echo calendar::render_highlighting_weekends($reports['highlighting_weekends']);

//if(pivot_calendars::has_access($reports['users_groups'], 'full')):

    $count_entities_with_access = 0;
    $reports_entities_query = db_query("select ce.*, e.name from app_ext_pivot_calendars_entities ce, app_entities e where e.id=ce.entities_id and ce.calendars_id='" . $reports['id'] . "' order by e.name");
    while($reports_entities_info = db_fetch_array($reports_entities_query))
    {
        if(users::has_users_access_name_to_entity('create', $reports_entities_info['entities_id']))
        {
            $reports_entities = $reports_entities_info;
            $count_entities_with_access++;
        }

        echo pivot_calendars::get_css($reports_entities_info);
    }

    if($count_entities_with_access == 1)
    {
//create default entity report for logged user
        $reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($reports_entities['entities_id']) . "' and reports_type='pivot_calendars" . $reports_entities['id'] . "'");
        $reports_info = db_fetch_array($reports_info_query);

        $entity_info = db_find('app_entities', $reports_entities['entities_id']);

        if($entity_info['parent_id'] > 0)
        {
            $add_url = url_for("reports/prepare_add_item", "redirect_to=pivot_calendars" . $reports_entities['id'] . "&reports_id=" . $reports_info['id']);
        }
        else
        {
            $add_url = url_for("items/form", "redirect_to=pivot_calendars" . $reports_entities['id'] . "&path=" . $reports_entities['entities_id']);
        }
    }
    else
    {
        $add_url = url_for('ext/pivot_calendars/add_item', 'calendars_id=' . $reports['id']);
    }
    ?>

    <script>

    <?php echo holidays::render_js_holidays() ?>

    var pivot_calendars<?= $reports['id'] ?>;
    document.addEventListener('DOMContentLoaded', function()
    {
        var calendarEl = document.getElementById('pivot_calendars<?= $reports['id'] ?>');
        pivot_calendars<?= $reports['id'] ?> = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            initialView: '<?= calendar::getInitialView($reports['default_view']) ?>',            
            selectable: <?= (pivot_calendars::has_access($reports['users_groups'], 'full') ? 'true':'false') ?>,
            editable: <?= (pivot_calendars::has_access($reports['users_groups'], 'full') ? 'true':'false') ?>,
            dayMaxEventRows: true,
            locale: '<?= calendar::getLocale() ?>',
            firstDay: '<?php echo CFG_APP_FIRST_DAY_OF_WEEK ?>',  
            slotMinTime: '<?php echo (strlen($reports['min_time']??'') ? $reports['min_time'] : "00:00") ?>',
            slotMaxTime: '<?php echo (strlen($reports['max_time']??'') ? $reports['max_time'] : "24:00") ?>',
            slotDuration: '<?php echo (strlen($reports['time_slot_duration']??'') ? $reports['time_slot_duration'] : '00:30:00') ?>',
            selectLongPressDelay: 100,
            aspectRatio: (is_mobile ? 0.75 : 1.35),
            eventSources: [                
                {
                  url: '<?= url_for("ext/pivot_calendars/view", "action=get_events&mode=full&id=" . $reports["id"]) ?>',
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
                $.ajax({type: "POST", url: "<?php echo url_for('ext/pivot_calendars/view', 'action=resize&id=' . $reports['id']) ?>", data: {id: e.event.id, end: e.event.end.toISOString(),view_name: e.view.type}});
            },
            eventDrop: function (e)
            {                                
                if(e.event.end)
                {
                    $.ajax({type: "POST", url: "<?php echo url_for('ext/pivot_calendars/view', 'action=drop&id=' . $reports['id']) ?>", data: {id: e.event.id, start: e.event.start.toISOString(), end: e.event.end.toISOString(),view_name: e.view.type}});
                }
                else
                {
                    $.ajax({type: "POST", url: "<?php echo url_for('ext/pivot_calendars/view', 'action=drop&id=' . $reports['id']) ?>", data: {id: e.event.id, start: e.event.start.toISOString()}});
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
                $(e.el).popover('hide') 
                clearTimeout(calendar_popover_timeout)                
            },
            loading: function (bool)
            {
                $('#pivot_calendars_loading<?= $reports['id'] ?>').toggle(bool);

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
        pivot_calendars<?= $reports['id'] ?>.render();        
    });

    </script>

