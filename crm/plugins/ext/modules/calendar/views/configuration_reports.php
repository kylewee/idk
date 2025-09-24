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

<h3 class="page-title"><?php echo TEXT_EXT_CALENDAR_REPORT ?></h3>

<p><?php echo TEXT_EXT_CALENDAR_REPORT_INFO ?></p>

<?php echo button_tag(TEXT_BUTTON_CREATE,url_for('ext/calendar/configuration_reports_form'),true) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th><?php echo TEXT_REPORT_ENTITY ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>    
    <th><?php echo TEXT_IN_MENU ?></th>
    <th><?php echo TEXT_EXT_СALENDAR_START_DATE ?></th>    
    <th><?php echo TEXT_EXT_СALENDAR_END_DATE ?></th>    
  </tr>
</thead>
<tbody>
<?php
$reports_query = db_query("select * from app_ext_calendar order by name");

$entity_cache = entities::get_name_cache();
$fields_cahce = fields::get_name_cache();

if(db_num_rows($reports_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($reports = db_fetch_array($reports_query)):


$panels_query = db_query("select * from app_filters_panels where entities_id='" . $reports['entities_id'] . "' and type='calendar_report_" . $reports['id'] . "'");
if(!$panels = db_fetch_array($panels_query))
{
    $sql_data = array(
        'position'=>'horizontal',
        'entities_id'=>$reports['entities_id'],
        'type'=>'calendar_report_' . $reports['id'],
        'is_active'=>1,
        'is_active_filters'=>1,
        'users_groups'=>'',
        'width'=>'',
        'sort_order'=>0,
    );
    
    db_perform('app_filters_panels',$sql_data);
    $panels_id = db_insert_id();
}
else
{
    $panels_id = $panels['id'];
}

?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/calendar/configuration_reports_delete','id=' . $reports['id'])) . ' ' . button_icon_edit(url_for('ext/calendar/configuration_reports_form','id=' . $reports['id'])) ?></td>
  <td><?php echo $entity_cache[$reports['entities_id']] ?></td>
  <td><?php 
      echo $reports['name'];
      
      if($reports['filters_panel']=='quick_filters')
      {
          echo '<br><small>&nbsp;&nbsp;' . TEXT_QUICK_FILTERS_PANELS . ': <a href="' . url_for('ext/filters_panels/fields','panels_id=' . $panels_id . '&entities_id=' . $reports['entities_id'] . '&redirect_to=calendar_report_' . $reports['id']) . '">' . TEXT_CONFIGURE . '</a></small>';
      }
      
      if($reports['reminder_status']==1)
      {
          $reports_id = default_filters::get_reports_id($reports['entities_id'], 'calendar_reminder' . $reports['id']);
          $count_filters = reports::count_filters_by_reports_id($reports_id);
          
          echo '<br><small>' . TEXT_EXT_REMINDER . ': ' . link_to(TEXT_FILTERS . ' (' . $count_filters . ')', url_for('default_filters/filters', 'reports_id=' . $reports_id . '&redirect_to=calendar_reminder' . $reports['id'])) . '</small>';
      }
  ?></td> 
  <td><?php echo render_bool_value($reports['in_menu']) ?></td> 
  <td><?php echo $fields_cahce[$reports['start_date']] ?></td>
  <td><?php echo $fields_cahce[$reports['end_date']] ?></td>    
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>


<?php
    //print_rr($app_calendar_reminder);
?>
