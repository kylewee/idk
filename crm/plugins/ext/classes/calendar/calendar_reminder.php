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

class calendar_reminder
{
    private $events, $reports, $check_time, $remind_later;
    
    function __construct()
    {                        
        $this->reports = [];
        $this->events = false;  
        $this->check_time = 0;
        $this->remind_later = [];
    }
    
    function init()
    {             
        $this->get_reports();
        
        $this->get_events();
        
        $this->send_events();
    }
    
    function reset()
    {
        $this->events = false;
    }
    
    function remind_later()
    {
        if(isset($_POST['remind_later']) and $_POST['remind_later']>0 and $this->events)
        {
            $this->remind_later[] = [
                'time' => time()+(_POST('remind_later')*60),
                'events' => $this->events
            ];
            
            $this->events = false;
        }
    }
    
    private function get_reports()
    {       
        global $app_user;
        
        $this->reports = [];
        
        if($app_user['group_id']>0)
        {
            $reports_query = db_query("select c.* from app_ext_calendar c, app_entities e, app_ext_calendar_access ca where e.id=c.entities_id  and c.id=ca.calendar_id and ca.access_groups_id='" . db_input($app_user['group_id']) . "' and reminder_status=1 and length(reminder_type)>0 order by c.name");
        }
        else
        {
            $reports_query = db_query("select c.* from app_ext_calendar c, app_entities e where e.id=c.entities_id and reminder_status=1 and length(reminder_type)>0 order by c.name");
        }
        
        while($reports = db_fetch_array($reports_query))
        {
            $this->reports[] = [
                'entities_id' => $reports['entities_id'],
                'id' => $reports['id'],
                'reminder_item_heading' => $reports['reminder_item_heading'],
                'start_date' => $reports['start_date'],
                'reminder_type' => $reports['reminder_type'],
                'reminder_minutes' => $reports['reminder_minutes'],
                'pivot' => '',
            ];
        }
        
        //add pivot calendar reports
        $reports_query = db_query("select e.*,c.users_groups from app_ext_pivot_calendars_entities e left join app_ext_pivot_calendars c on e.calendars_id =c.id where e.reminder_status=1 and length(e.reminder_type)>0");        
        while($reports = db_fetch_array($reports_query))
        {
            if(pivot_calendars::has_access($reports['users_groups']))
            {
                $this->reports[] = [
                    'entities_id' => $reports['entities_id'],
                    'id' => $reports['id'],
                    'reminder_item_heading' => $reports['reminder_item_heading'],
                    'start_date' => $reports['start_date'],
                    'reminder_type' => $reports['reminder_type'],
                    'reminder_minutes' => $reports['reminder_minutes'],
                    'pivot' => '_pivot',
                ];
            }
        }
        
        //print_rr($this->reports);
    }
    
    private function get_events()
    {
        global $app_calendar_remidner_events;
        
        $current_time = date("Y-m-d H:i",time());
                        
        //set check time (prevent open popup in the same minute)
        if($this->check_time==$current_time)
        {
            return false;
        }
        else
        {
            $this->check_time=$current_time;
        }
        
        //check if remind_later exist
        if(count($this->remind_later))
        {
            foreach($this->remind_later as $k=>$v)
            {
                if(date("Y-m-d H:i",$v['time'])==$current_time)
                {
                   $this->events = $v['events'];
                   
                    //remove reminde later events
                   unset($this->remind_later[$k]);
                }
            }
        }
        
        if(is_array($this->events))
        {
            $popup = $this->events['popup'];
            $push = $this->events['push'];
        }
        else
        {
            $popup = $push = [];
        }
        
        foreach($this->reports as $report)
        {
            $start_date = $report['start_date'];            
            
            $query = new items_query($report['entities_id'],[
                'where' => "and FROM_UNIXTIME(field_{$start_date},'%Y-%m-%d %H:%i')='" . date("Y-m-d H:i",time()+($report['reminder_minutes']*60)) . "'",
                'report_id' => default_filters::get_reports_id($report['entities_id'], 'calendar_reminder' . $report['pivot'] . $report['id']),
                'fields_in_query' => $report['reminder_item_heading'],
                'add_filters' => true,
                'add_formula' => true,                        
            ]);
                
            $item_query = db_query($query->get_sql(),false);
            while($item = db_fetch_array($item_query))
            {
                $item['entity_id'] = $report['entities_id'];
                
                if(strlen($report['reminder_item_heading']))
                {
                    $fieldtype_text_pattern = new fieldtype_text_pattern();
                    $title = $fieldtype_text_pattern->output_singe_text($report['reminder_item_heading'],$report['entities_id'],$item);
                }
                else
                {
                    $title = items::get_heading_field($report['entities_id'], $item['id'],$item);
                }
                
                $data = [
                    'id' => $item['id'],
                    'entity_id' => $item['entity_id'],
                    'title'=> $title,
                ];
                
                if(strstr($report['reminder_type'],'popup') and !$this->has_item($item,'popup'))
                {
                    $popup[] = $data;
                }
                
                if(strstr($report['reminder_type'],'push')and !$this->has_item($item,'push'))
                {
                    $push[] = $data;
                }                                
            }
        }
        
        if(count($popup) or count($push))
        {            
            $this->events = [
                'popup' => $popup,
                'push'  => $push,                                
                'is_push_sent' =>false,
            ];
        }
    }
    
    private function has_item($item, string $type): bool
    {                
        if($this->events)
        {
            foreach($this->events[$type] as $event)
            {
                if($item['id']==$event['id'] and $item['entity_id']==$event['entity_id'])
                {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function send_events()
    {                
        if($this->events)
        {                          
            //print_rr($event);            
            $popup_html = '';
            
            if(count($this->events['popup']))
            {
                $html = '<div class="list-group">';
                foreach($this->events['popup'] as $item)
                {
                    $html .= '<a class="list-group-item" href="' . url_for('items/info','path=' . $item['entity_id'] . '-' . $item['id'] . '&reset_calendar_reminder=1'). '">' . $item['title'] . '</a>';
                }
                $html .= '</div>';
                
                $popup_html = '
                   <div class="calendar_remidner_popup">
                        <h4 class="modal-title">' . TEXT_EXT_REMINDER . '</h4>
                        <hr>
                        ' . $html . ' 
                        <hr>
                        <a href="javascript: calendar_remidner_confirm()" class="btn btn-primary"><i class="fa fa-check"></i> ' . TEXT_EXT_CONFIRM. '</a>
                        <a href="javascript: calendar_remidner_remind_later()" class="btn btn-default">' . TEXT_EXT_REMIND_AFTER . '</a>
                        ' . input_tag('remind_later','5',['class'=>'form-control','type'=>'number']) . ' ' . TEXT_EXT_MINUTES_AFTER .'     
                   </div>
                   ';  
                
                $this->popup_html($popup_html);
            }
            
            $push_html = '';
            if(count($this->events['push']))
            {
                $items = [];
                $url = false;
                foreach($this->events['popup'] as $item)
                {
                    $items[] = strip_tags(str_replace(["\n\r","\n","\r"],' ',$item['title']));
                    
                    if(!$url)
                    {
                        $url = url_for('items/info','path=' . $item['entity_id'] . '-' . $item['id'] . '&reset_calendar_reminder=1');
                    }
                }
                
                $push_html = implode('\n',$items);
                
                $this->push_html($push_html,$url);
            }
                                                
        }
    }
    
    private function push_html($html, string $url)
    {
        if(!$this->events['is_push_sent'])
        {
            $this->events['is_push_sent'] = true;
            
            echo '
                <script>                      
                    if (("Notification" in window)) 
                    {
                        Notification.requestPermission(function(permission){ });

                        if (Notification.permission === "granted") 
                        {
                            var notification = new Notification("' . addslashes(TEXT_EXT_REMINDER) . '",{
                                dir: app_language_text_direction,
                                body: "' . addslashes($html) . '",                            
                                });
                            notification.onclick = function(event){                                
                              event.preventDefault(); // prevent the browser from focusing the Notification\'s tab
                              window.open("' . $url . '", "_new");  
                            }  
                        }
                    }
                </script>';
        }
    }
    
    private function popup_html($html)
    {
        if(!strlen($html)) return false;
        
        echo '
            <script>
                $.fancybox("' . addslashes(str_replace(["\n\r","\n","\r"],"",$html)) . '",{
                    modal: true,                      
                })

                function calendar_remidner_confirm()
                {
                    $.fancybox.close()
                    $.ajax({url: url_for("dashboard/dashboard","action=calendar_reminder_confirm")})
                }

                function calendar_remidner_remind_later()
                {
                    $.fancybox.close()
                    $.ajax({type:"POST",url: url_for("dashboard/dashboard","action=calendar_remind_later"),data:{remind_later:$("#remind_later").val()}})
                }
            </script>
            ';
    }    
}




