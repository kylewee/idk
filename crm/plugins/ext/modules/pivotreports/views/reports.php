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

<h3 class="page-title"><?php echo TEXT_EXT_PIVOTREPORTS ?></h3>

<p><?php echo sprintf(TEXT_REPORT_IS_OUTDATED,url_for('ext/pivot_tables/reports'))?></p>

<?php echo button_tag(TEXT_BUTTON_CREATE,url_for('ext/pivotreports/reports_form'),true) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th><?php echo TEXT_REPORT_ENTITY ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>    
    <th><?php echo TEXT_SORT_ORDER ?></th>           
  </tr>
</thead>
<tbody>
<?php
$reports_query = db_query("select * from app_ext_pivotreports order by sort_order, name");

$entities_cache = entities::get_name_cache();
$fields_cahce = fields::get_name_cache();


if(db_num_rows($reports_query)==0) echo '<tr><td colspan="4">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($reports = db_fetch_array($reports_query)):
?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/pivotreports/reports_delete','id=' . $reports['id'])) . ' ' . button_icon_edit(url_for('ext/pivotreports/reports_form','id=' . $reports['id'])) . ' ' . button_icon(TEXT_EXT_PIVOTREPORTS_FIELDS,'fa fa-cogs',url_for('ext/pivotreports/fields','id=' . $reports['id']),false) ?></td>
  <td><?php echo $entities_cache[$reports['entities_id']]??'' ?></td>
  <td><?php echo link_to($reports['name'],url_for('ext/pivotreports/view','id=' . $reports['id'])) ?></td>  
  <td><?php echo $reports['sort_order'] ?></td>   
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>