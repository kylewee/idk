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

<h3 class="page-title"><?php echo TEXT_EXT_RESOURCE_TIMELINE ?></h3>

<p><?php echo TEXT_EXT_RESOURCE_TIMELINE_INFO ?></p>

<?php echo button_tag(TEXT_BUTTON_CREATE,url_for('ext/resource_timeline/form'),true) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>    
    <th><?php echo TEXT_ENTITY . ' (' . TEXT_EXT_RESOURCE . ')' ?></th>        
    <th width="100%"><?php echo TEXT_NAME ?></th>    
    <th><?php echo TEXT_IN_MENU ?></th>        
    <th><?php echo TEXT_SORT_ORDER ?></th>
  </tr>
</thead>
<tbody>
<?php
$reports_query = db_query("select * from app_ext_resource_timeline order by sort_order,name");

if(db_num_rows($reports_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($reports = db_fetch_array($reports_query)):

$count_query = db_query("select count(*) as total from app_ext_resource_timeline_entities where calendars_id='" . $reports['id'] . "'");
$count = db_fetch_array($count_query);
?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/resource_timeline/delete','id=' . $reports['id'])) . ' ' . button_icon_edit(url_for('ext/resource_timeline/form','id=' . $reports['id'])) ?></td>  
  <td>
    <?php 
        $filters_panel_type = 'resource_timeline_filters_panel_' . $reports['id'];
        $filters_panel_id = filters_panels::get_id_by_type($reports['entities_id'], $filters_panel_type);
        $filters_panel_link = '<a href="' . url_for('ext/filters_panels/fields','panels_id=' . $filters_panel_id . '&entities_id=' . $reports['entities_id'] . '&redirect_to=resource_timeline' . $reports['id']) . '">' . TEXT_FILTERS_PANELS . '</a>';
        
        echo link_to(entities::get_name_by_id($reports['entities_id']),url_for('default_filters/filters','reports_id=' . default_filters::get_reports_id($reports['entities_id'], 'resource_timeline' . $reports['id']) . '&redirect_to=resource_timeline'. $reports['id'])) . 
        '<br>' . tooltip_text(TEXT_FILTERS . ': ' . reports::count_filters_by_reports_type($reports['entities_id'],'resource_timeline' . $reports['id']) . ' | ' . $filters_panel_link);
        
    ?>
  </td>
  <td><?php echo link_to($reports['name'],url_for('ext/resource_timeline/entities','calendars_id=' . $reports['id'])) . '<br>' . tooltip_text(TEXT_EXT_ENTITIES . ': ' . $count['total']) ?></td> 
  <td><?php echo render_bool_value($reports['in_menu']) ?></td>      
  <td><?php echo $reports['sort_order'] ?></td>
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>