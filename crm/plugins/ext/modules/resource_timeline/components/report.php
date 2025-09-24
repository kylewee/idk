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

<?php echo resource_timeline::render_legend($reports) ?>

<div id="resource_timeline_loading<?php echo $reports['id'] ?>" class="loading_data"></div>
<div id="resource_timeline<?php echo $reports['id'] ?>" ></div>

<br>

<script>

  var resource_timeline<?= $reports['id'] ?>;
    document.addEventListener('DOMContentLoaded', function()
    {
        var calendarEl = document.getElementById('resource_timeline<?= $reports['id'] ?>');
        resource_timeline<?= $reports['id'] ?> = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            initialView: '<?= resource_timeline::get_default_view($reports) ?>',            
            selectable: <?= (resource_timeline::is_selectable($reports) ? 'true':'false') ?>,
            editable: <?= (resource_timeline::has_access($reports['users_groups'],'full') ? 'true':'false') ?>,
            dayMaxEventRows: true,
            timeZone: '<?= CFG_APP_TIMEZONE ?>',
            locale: '<?= calendar::getLocale() ?>',
            firstDay: '<?php echo CFG_APP_FIRST_DAY_OF_WEEK ?>',  
            slotMinTime: '<?php echo (strlen($reports['min_time']) ? $reports['min_time'] : "00:00") ?>',
            slotMaxTime: '<?php echo (strlen($reports['max_time']) ? $reports['max_time'] : "24:00") ?>',
            slotDuration: '<?php echo resource_timeline::get_slot_duration($reports) ?>',
            selectLongPressDelay: 100,
            height: <?php echo resource_timeline::get_height() ?>,     
            eventSources: [                
                {
                  url: '<?= url_for('ext/resource_timeline/view','action=get_events&id=' . $reports['id']) ?>',
                  method: 'POST',
                  failure: function() {
                    alert('<?= TEXT_ERROR_LOADING_DATA ?>');
                  }
                }               
            ],   
            resources:{
                url: '<?= url_for('ext/resource_timeline/view','action=get_resources&id=' . $reports['id']) ?>',
                method: 'POST'                  
            },   
            resourceOrder: "<?=  resource_timeline::get_resources_order($reports) ?>",
            resourceGroupField: "<?=  resource_timeline::get_resources_group_field($reports) ?>",
            resourceAreaColumns: <?php echo resource_timeline::get_resources_columns($reports) ?>,
            resourceAreaWidth: "<?php echo resource_timeline::get_area_width($reports) ?>",
            eventResourceEditable:false,
            views: {
                timelineYear1: {
                  buttonText: '<?php echo TEXT_EXT_YEAR ?>',
                  type: 'resourceTimeline',
                  duration: { year: 1 },
                  slotDuration: { months: 1 }
                },
                timelineYear2: {
                  buttonText: '<?php echo TEXT_EXT_YEAR  . '(2)' ?>',
                  type: 'resourceTimeline',
                  duration: { year: 2 },
                  slotDuration: { months: 1 }
                },
                timelineMonth2: {
                  buttonText: '<?php echo TEXT_EXT_MONTH . ' (2)' ?>',
                  type: 'resourceTimeline',
                  duration: { months: 2 },
                  slotDuration: { day: 1 }
                }, 
                timelineMonth3: {
                  buttonText: '<?php echo TEXT_EXT_MONTH . ' (3)' ?>',
                  type: 'resourceTimeline',
                  duration: { months: 3 },
                  slotDuration: { day: 1 }
                },    
                timelineWeek6:{
                  buttonText: '<?php echo TEXT_EXT_WEEK . ' (4)' ?>',
                  type: 'resourceTimeline',
                  duration: { weeks: 4 },
                  slotDuration: { weeks: 1 },
                  weekNumbers: true,
                  weekNumberFormat: { 
                      week: 'numeric' 
                  },
                }        
            },                      
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '<?php echo resource_timeline::get_view_modes($reports) ?>'                
            },            
            eventTimeFormat: { 
                hour: '2-digit',
                minute: '2-digit',                
                meridiem: false
            },                    
            select: function (e)
            {                
                open_dialog('<?php echo  resource_timeline::get_add_url($reports) ?>' + '&start=' + encodeURIComponent(e.startStr) + '&end=' + encodeURIComponent(e.endStr) + '&view_name=' + e.view.type+'&resource_id='+e.resource.id)
            },
            eventResize: function (e)
            {                      
                $.ajax({type: "POST", url: "<?php echo url_for('ext/resource_timeline/view','action=resize&id=' . $reports['id']) ?>", data: {id: e.event.extendedProps.item_id, end: e.event.end.toISOString(), reports_entities_id: e.event.extendedProps.reports_entities_id, view_name: e.view.type}});
            },
            eventDrop: function (e)
            {                                
                if(e.event.end)
                {
                    $.ajax({type: "POST", url: "<?php echo url_for('ext/resource_timeline/view','action=drop&id=' . $reports['id']) ?>", data: {id: e.event.extendedProps.item_id, start: e.event.start.toISOString(), reports_entities_id:e.event.extendedProps.reports_entities_id, end: e.event.end.toISOString(),view_name: e.view.type}});
                }
                else
                {
                    $.ajax({type: "POST", url: "<?php echo url_for('ext/resource_timeline/view','action=drop&id=' . $reports['id']) ?>", data: {id: e.event.extendedProps.item_id, start: e.event.start.toISOString(), reports_entities_id:e.event.extendedProps.reports_entities_id}});
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
                $(e.el).popover('hide')    
                clearTimeout(calendar_popover_timeout)                
            },
            loading: function (bool)
            {
                $('#resource_timeline_loading<?= $reports['id'] ?>').toggle(bool);

                if (!bool)
                {                    
                    fc_calendar_button(this.el.getAttribute('id'))                                           
                }
            },
            resourcesSet: function(resources)
            {
                if(resources.length)
                {
                    for (const [key, value] of Object.entries(resources)) 
                    {
                        if(value._resource.extendedProps.popup.length==0) return;
                        
                        //console.log(value._resource);
                        //console.log(value._resource.id);
                                                                        
                        obj = $('[data-resource-id="'+value._resource.id+'"] .fc-datagrid-cell-main').first()
                        if(obj.length!=-1)
                        {
                            if(!obj.hasClass('info-icon'))
                            {
                                obj.addClass('info-icon')
                                
                                $(obj).prepend(
                                    $('<i class="fa fa-info-circle" aria-hidden="true" style="padding-right:5px;"></i>').popover({              
                                      content: value._resource.extendedProps.popup,
                                      trigger: 'hover',
                                      placement: 'bottom',
                                      container: 'body',
                                      html: true,
                                    })
                                );
                            }
                        }
                    }
                }
            }
            
        });
        resource_timeline<?= $reports['id'] ?>.render();        
    });
   
</script>
