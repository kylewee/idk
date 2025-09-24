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

    $module_info_query = db_query("select * from app_ext_modules where type='telephony' and is_active=1 limit 1");
    if($module_info = db_fetch_array($module_info_query))
    {
        modules::include_module($module_info,'telephony');

        $telephony_module = new $module_info['module'];
    }  
?>


<table class="table table-striped table-bordered table-hover" style="margin-bottom:0">
    <thead>
        <tr>
            <th colspan="2"></th>
            <th width="45"><?php echo TEXT_TYPE ?></th>            
            <th width="140"><?php echo TEXT_DATE_ADDED ?></th>    
            <th><?php echo TEXT_PHONE ?></th>
            <th><?php echo TEXT_NAME ?></th>    
            <th><?php echo TEXT_EXT_ANSWERED ?></th>    
            <th><?php echo TEXT_EXT_DURATION ?></th>
            <th width="40"></th>
            <th width="40"></th>
            
<?php
if(strlen(CFG_CALL_HISTORY_ENTITIES))
{
    $entities_query = db_query("select * from app_entities where id in (" . db_input_in(CFG_CALL_HISTORY_ENTITIES). ") order by sort_order, name");
    while($entities = db_fetch_array($entities_query))
    {
        if(users::has_users_access_to_entity($entities['id']))
        {
            echo '<th>' . link_to($entities['name'],url_for('items/items','path=' . $entities['id']),['target'=>'_blank']) . '</th>';
        }
    }
}
?>
        </tr>
    </thead>
    <tbody>

<?php

//print_rr($_POST);

$where_sql = '';
		    
foreach($_POST['filters'] as $filter)
{
    if(strlen($filter['value']) > 0)
    {
        switch($filter['name'])
        {
            case 'from':
                $where_sql .= " and FROM_UNIXTIME(date_added,'%Y-%m-%d')>='" . $filter['value'] . "'";
                break;
            case 'to':
                $where_sql .= " and FROM_UNIXTIME(date_added,'%Y-%m-%d')<='" . $filter['value'] . "'";
                break;
            case 'direction':
                if($filter['value']=='stared')
                {
                    $where_sql .= " and is_star=1";
                }
                else
                {
                    $where_sql .= " and direction='" . $filter['value'] . "'";
                }
                break;
            case 'duration':
                if($filter['value']=='unheard')
                {
                    $where_sql .= " and duration>0 and is_new=1";
                }
                else
                {
                    $where_sql .= " and duration" . ($filter['value']==1 ? '>0':'=0') . "";
                }
                break;
            case 'search':
                $value = substr($filter['value'],0,32);
                $where_sql .= " and (phone like '%" . db_input($value) . "%' or client_name like '%" . db_input($value) . "%')";
                break;            
            
        }
    }
}


$listing_sql = "select * from app_ext_call_history where type='phone' {$where_sql} order by id desc";

//echo $listing_sql;

$listing_split = new split_page($listing_sql,'call_history_listing','',CFG_APP_ROWS_PER_PAGE);

$items_query = db_query($listing_split->sql_query);								
        
if(!db_num_rows($items_query))
{
    echo '<tr><td colspan="15">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';
}

$html = '';
while($items = db_fetch_array($items_query))
{
    $current_page = (isset($_POST['page']) ? _POST('page'):1);
    
    $current_phone = preg_replace("/\D/","",$items['phone']);
    
    //entities
    $related_entities = '';
    if(strlen(CFG_CALL_HISTORY_ENTITIES))
    {
        $entities_query = db_query("select * from app_entities where id in (" . db_input_in(CFG_CALL_HISTORY_ENTITIES). ") order by sort_order, name");
        while($entities = db_fetch_array($entities_query))
        {
            if(users::has_users_access_to_entity($entities['id']))
            {
                $where_sql = [];
                $client_phone = [];
                foreach($app_fields_cache[$entities['id']] as $field)
                {
                    if($field['type']=='fieldtype_phone')
                    {
                        if(!count($client_phone)) $client_phone[$field['id']] = $items['phone'];
                        
                        
                        //$where_sql[] = "rukovoditel_regex_replace('[^0-9]','',field_{$field['id']})='{$current_phone}'";
                        $where_sql[] = "REGEXP_REPLACE(field_{$field['id']},'[^0-9]','')='{$current_phone}'";
                    }
                }
                
                if(count($where_sql))
                {
                    $item_query = db_query("select id from app_entity_{$entities['id']} where (" . implode(' or ', $where_sql).  ")");
                    $count = db_num_rows($item_query);                    
                                        
                    $add_url = url_for('items/form','path=' . $entities['id'] . '&fields[' . key($client_phone). ']=' . urlencode(current($client_phone)) . '&redirect_to=call_history' . $current_page );
                    
                    if($count==0)
                    {
                        $related_entities .= '<td>' . link_to_modalbox(TEXT_ADD, $add_url). '</td>';
                    }
                    elseif($count==1)
                    {
                        $item = db_fetch_array($item_query);
                        
                        $related_entities .= '<td><a href="' . url_for('items/info','path=' . $entities['id'] . '-' . $item['id']). '" target="_blank">' . items::get_heading_field($entities['id'], $item['id']) . '</a>  ' . link_to_modalbox('<i class="fa fa-plus-square"></i>', $add_url) . '</td>';
                    }
                    else
                    {
                        $related_entities .= '<td>' . link_to_modalbox( TEXT_TOTAL. ' (' . $count . ')', url_for('ext/call_history/items','entity_id=' . $entities['id'] . '&id=' . $items['id'])). ' ' . link_to_modalbox('<i class="fa fa-plus-square"></i>', $add_url) . '</td>';
                    }
                    
                }
                else
                {
                    $related_entities .= '<td></td>';
                }
                
                //print_rr($where_sql);                
            }
        }
    }
    
    //phone
    $phone = $items['phone'];
    
    if(isset($telephony_module))
    {
        $phone = $telephony_module->call_history_url($module_info['id'],$phone);
    }
    
    $play = (strlen($items['recording']) ? link_to_modalbox('<i class="fa fa-play-circle play-circle-action ' . ($items['is_new']==0 ? 'play-circle-off':'') . '"></i>', url_for('ext/call_history/play','id=' . $items['id'] . '&page=' . $current_page )):'');
            
    $html .= '
        <tr class="' . ($items['duration']==0 ? 'danger': ($items['direction']=='in' ? 'info':'')). '">
            <td width="22"><i class="fa fa-trash-o pointer btn-action-delete" data_id="' . $items['id'] . '" title="' . TEXT_DELETE . '"></i></td>
            <td width="22"><i class="fa fa-star pointer mail-star btn-action-star ' . ($items['is_star']==1 ? 'mail-star-active':'') . '" data_id="' . $items['id'] . '"></i></td>
            <td align="center">' . ($items['direction']=='in' ? '<i class="fa fa-arrow-circle-right" title="' . TEXT_EXT_INCOMING_CALL . '"></i>':'<i class="fa fa-arrow-circle-o-left" title="' . TEXT_EXT_OUTGOING_CALL . '"></i>') . '</td>
            <td>' . format_date_time($items['date_added']). '</td>            
            <td>' . $phone . '</td>
            <td>' . $items['client_name'] . '</td>
            <td>' . ($items['duration']>0 ? TEXT_YES : TEXT_NO) . '</td>
            <td>' . seconds_to_time_format($items['duration']) . '</td>
            <td style="text-align:center">' . tooltip_icon($items['comments'],'left','fa-comment') . '</td>
            <td align="center">' . $play . '</td>
            ' . $related_entities . '    
        </tr>
        ';
}

echo $html;
?>
    </tbody>
</table>

<?php
$html = '
        <table width="100%">
          <tr>
            <td>' . $listing_split->display_count() . '</td>
            <td align="right">' . $listing_split->display_links() . '</td>
          </tr>
        </table>
    ';
echo $html;
?>

