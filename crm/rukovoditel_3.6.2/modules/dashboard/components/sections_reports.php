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

$reports_id = str_replace(entities_menu::get_reports_types(), '', $section_report);

switch(true)
{
    case strstr($section_report, 'standard'):
        $reports_query = db_query("select * from app_reports where created_by='" . db_input($app_logged_users_id) . "' and id='" . db_input($reports_id) . "'");
        if($reports = db_fetch_array($reports_query))
        {
            if(users::has_access_to_entity($reports['entities_id'],'reports'))
            {
                require(component_path('dashboard/render_standard_reports'));
            }                        
        }
        break;
    case strstr($section_report, 'common'):
        $reports_query = db_query("select * from app_reports where find_in_set(" . $app_user['group_id'] . ",users_groups) and id='" . db_input($reports_id) . "'");
        if($reports = db_fetch_array($reports_query))
        {
            require(component_path('dashboard/render_standard_reports'));
        }
        break;
    case strstr($section_report, 'graphicreport'):
        $reports_query = db_query("select * from app_ext_graphicreport where id='" . $reports_id . "'");
        if($reports = db_fetch_array($reports_query))
        {
            if(in_array($app_user['group_id'], explode(',', $reports['allowed_groups'])) or $app_user['group_id'] == 0)
            {
                echo '<h3 class="page-title"><a href="' . url_for('ext/graphicreport/view', 'id=' . $reports['id']) . '">' . $reports['name'] . '</a></h3>';

                require(component_path('ext/graphicreport/view'));
            }
        }
        break;
    case strstr($section_report, 'funnelchart'):
        $reports_query = db_query("select * from app_ext_funnelchart where id='" . $reports_id . "'");
        while($reports = db_fetch_array($reports_query))
        {
            if(in_array($app_user['group_id'], explode(',', $reports['users_groups'])) or $app_user['group_id'] == 0)
            {
                echo '<h3 class="page-title"><a href="' . url_for('ext/funnelchart/view', 'id=' . $reports['id']) . '">' . $reports['name'] . '</a></h3>';
                require(component_path('ext/funnelchart/view'));
            }
        }
        break;

    case strstr($section_report, 'pivot_tables'):
        $reports_query = db_query("select * from app_ext_pivot_tables where id='" . $reports_id . "'");
        if($pivot_tables = db_fetch_array($reports_query))
        {
            echo '<h3 class="page-title"><a href="' . url_for('ext/pivot_tables/view', 'id=' . $pivot_tables['id']) . '">' . $pivot_tables['name'] . '</a></h3>';
            $pivot_table = new pivot_tables($pivot_tables);
            require(component_path('ext/pivot_tables/pivot_table'));
        }
        break;
    case strstr($section_report, 'pivotreports'):
        $reports_query = db_query("select * from app_ext_pivotreports where id='" . $reports_id . "'");
        if($pivotreports = db_fetch_array($reports_query))
        {
            if(in_array($app_user['group_id'], explode(',', $pivotreports['allowed_groups'])) or $app_user['group_id'] == 0)
            {
                echo '<h3 class="page-title"><a href="' . url_for('ext/pivotreports/view', 'id=' . $pivotreports['id']) . '">' . $pivotreports['name'] . '</a></h3>';
                echo '
						<style>
							.pvtVals{
								display:none;
							}
							.pvtRendererTD{
								display:none;
							}
						</style>
						';

                //allow edit
                $pivotreports = pivotreports::apply_allow_edit($pivotreports);

                require(component_path('ext/pivotreports/pivottable'));
            }
        }
        break;
    case strstr($section_report, 'calendar_personal'):
        echo '<h3 class="page-title"><a href="' . url_for('ext/calendar/personal') . '">' . TEXT_EXT_MY_СALENDAR . '</a>' . icalendar::get_url(CFG_PERSONAL_CALENDAR_ICAL,'personal') . '</h3>';
        require(component_path('ext/calendar/personal'));
        break;
    case strstr($section_report, 'calendar_public'):
        echo '<h3 class="page-title"><a href="' . url_for('ext/calendar/public') . '">' . TEXT_EXT_СALENDAR . '</a> ' . icalendar::get_url(CFG_PUBLIC_CALENDAR_ICAL,'public') . '</h3>';
        require(component_path('ext/calendar/public'));
        break;
    case strstr($section_report, 'calendarreport'):
        if($app_user['group_id'] > 0)
        {
            $reports_query = db_query("select c.* from app_ext_calendar c, app_entities e, app_ext_calendar_access ca where c.id='" . $reports_id . "' and e.id=c.entities_id and c.id=ca.calendar_id and ca.access_groups_id='" . db_input($app_user['group_id']) . "' order by c.name");
        }
        else
        {
            $reports_query = db_query("select c.* from app_ext_calendar c, app_entities e where c.id='" . $reports_id . "' and  e.id=c.entities_id order by c.name");
        }
        if($reports = db_fetch_array($reports_query))
        {
            echo '<h3 class="page-title"><a href="' . url_for('ext/calendar/report', 'id=' . $reports['id']) . '">' . $reports['name'] . '</a> ' . icalendar::get_url($reports['enable_ical'],'report',$reports['id']). '</h3>';
            require(component_path('ext/calendar/report'));
        }
        break;
    case strstr($section_report, 'pivot_calendars'):
        $reports_query = db_query("select * from app_ext_pivot_calendars where id='" . $reports_id . "'");
        if($reports = db_fetch_array($reports_query))
        {
            if(pivot_calendars::has_access($reports['users_groups']))
            {
                echo '<h3 class="page-title"><a href="' . url_for('ext/pivot_calendars/view', 'id=' . $reports['id']) . '">' . $reports['name'] . '</a>' . icalendar::get_url($reports['enable_ical'],'pivot_report',$reports['id']) . '</h3>';
                require(component_path('ext/pivot_calendars/report'));
            }
        }
        break;
    case strstr($section_report, 'resource_timeline'):
        $reports_query = db_query("select * from app_ext_resource_timeline where id='" . $reports_id . "'");
        if($reports = db_fetch_array($reports_query))
        {
            echo '<h3 class="page-title"><a href="' . url_for('ext/resource_timeline/view', 'id=' . $reports['id']) . '">' . $reports['name'] . '</a></h3>';            
            require(component_path('ext/resource_timeline/report'));
        }
        break;
        
    case strstr($section_report, 'report_page'):
        $reports_query = db_query("select * from app_ext_report_page where id='" . $reports_id . "'");
        if($reports = db_fetch_array($reports_query))
        {
            $settings = new settings($reports['settings']);
            
            $html = '';
            if($settings->get('hide_page_title')!=1)
            {
                $html = '<h3 class="page-title"><a href="' . url_for('report_page/view', 'id=' . $reports['id']) . '">' . $reports['name'] . '</a></h3>'; 
            }
            
            $filters = new report_page\report_filters($reports);
            $filters->set_form_css('from-report-page-filters from-report-page-filters' . $reports['id']);
            $filters->set_form_function('load_report_page' . $reports['id'] . '()');
            $html .= $filters->render();
            
            $html .= '<div class="row">
                        <div class="col-md-12">
                            <div id="report_page_content' . $reports['id'] . '" data-id="' . $reports['id'] . '" data-path=""></div>
                        </div>
                    </div>

                    <script>
                    function load_report_page' . $reports['id'] . '()
                    {
                        $("#report_page_content' . $reports['id'] . '").css("opacity", 0.5).append(\'<div class="data_listing_processing"></div>\')

                        let data = $("#report_page_content' . $reports['id'] . '").data()    

                        $("#report_page_content' . $reports['id'] . '").load(url_for("report_page/view","action=load&id="+data.id+"&path="+data.path),$(".from-report-page-filters' . $reports['id'] . '").serializeArray(),function(){
                            $(this).css("opacity", 1)

                            app_handle_scrollers()
                        })
                    }

                    $(function(){
                        load_report_page' . $reports['id'] . '()
                    })

                    </script>';
            
            echo $html;
            
            
        }
        break;
    case strstr($section_report, 'kanban'):
        $reports_query = db_query("select * from app_ext_kanban where is_active=1 and id='" . $reports_id . "' and (find_in_set(" . $app_user['group_id'] . ",users_groups) or find_in_set(" . $app_user['id'] . ",assigned_to))");
        if($reports = db_fetch_array($reports_query))
        {
            echo '<h3 class="page-title"><a href="' . url_for('ext/kanban/view', 'id=' . $reports['id']) . '">' . $reports['name'] . '</a></h3>';            
            
            $html = '
                <div id="kanban_board' . $reports['id']. '"></div>    
                <script>
                    function load_kanban_report' . $reports['id']. '()
                    {
                        let content_id = "#kanban_board' . $reports['id']. '"
                        $(content_id).append("<div class=\"data_listing_processing\"></div>");    
                        $(content_id).css("opacity", 0.5);
                        $(content_id).load("' . url_for('ext/kanban/view','action=kanban&id=' . $reports['id']) . '",function(){
                            $(this).css("opacity", 1);
                            $(".kanban-div",this).floatingScroll()            
                        })
                    }

                    function load_kanban_report' . $reports['id']. '_items(choice_id,page)
                    {
                        let content_id = "#kanban' . $reports['id'] . '_"+choice_id+"_items"
                        $(content_id).css("opacity", 0.5);    
                        $(content_id).load("' . url_for('ext/kanban/view','action=kanban_items&id=' . $reports['id']) . '&page="+page+"&choice_id="+choice_id,function(){
                            $(this).css("opacity", 1);
                            load_kanban_report' . $reports['id'] . '_sortable()
                        })
                    }

                    load_kanban_report' . $reports['id']. '()
                </script>    
                    ';
            echo $html;
        }
        break;    
}