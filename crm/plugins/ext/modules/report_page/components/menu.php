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


$reports_query = db_query("select * from app_ext_report_page where entities_id=0 and (find_in_set({$app_user['group_id']},users_groups) or find_in_set({$app_user['id']},assigned_to)) and is_active=1 order by sort_order, name");
while ($reports = db_fetch_array($reports_query))
{    
    $check_query = db_query("select id from app_entities_menu where find_in_set('report_page" . $reports['id'] . "',reports_list)");
    if (!$check = db_fetch_array($check_query))
    {
        $app_plugin_menu['reports'][] = array(
            'title' => $reports['name'], 
            'url' => url_for('report_page/view', 'id=' . $reports['id']),
            'class' => $reports['icon'],
            'icon_color' => $reports['icon_color'],
            );
    }    
}

if(strlen($app_path))
{
    $path_array = [];
    foreach(explode('/',$app_path) as $v)
    {
        $k = explode('-',$v);
        $path_array[$k[0]] = $k[1]??0;
    }
            
    $reports_query = db_query("select * from app_ext_report_page where entities_id>0 and type='page' and (find_in_set({$app_user['group_id']},users_groups) or find_in_set({$app_user['id']},assigned_to)) and is_active=1 order by sort_order, name");
    while ($reports = db_fetch_array($reports_query))
    {       
        if(isset($path_array[$reports['entities_id']]) and $path_array[$reports['entities_id']]>0)
        {
            $items_filters = new items_filters($reports['entities_id'], $path_array[$reports['entities_id']]);
            if($items_filters->check(['report_type'=>'report_page' . $reports['id']]))
            {
                $app_plugin_menu['items_menu_reports'][] = array(
                    'title' => (strlen($reports['icon']) ? app_render_icon($reports['icon'], '', $reports['icon_color']) . ' ':'') . $reports['name'] , 
                    'url' => url_for('report_page/view', 'id=' . $reports['id'] . '&path=' . $reports['entities_id'] . '-' . $path_array[$reports['entities_id']]),                    
                    );        
            }
        }
    }
}