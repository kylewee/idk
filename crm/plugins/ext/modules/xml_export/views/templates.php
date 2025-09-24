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

<h3 class="page-title"><?php echo TEXT_EXT_XML_EXPORT ?></h3>

<p><?php echo TEXT_EXT_XML_EXPORT_INFO ?></p>

<?php
  $where_sql = '';
  
  if($xml_templates_filter>0)
  {
    $where_sql .= "where ep.entities_id='" . db_input($xml_templates_filter) . "'";
  }
  
  $templates_query = db_query("select ep.*, e.name as entities_name from app_ext_xml_export_templates ep left join app_entities e on e.id=ep.entities_id  " . $where_sql . " order by e.id, ep.sort_order, ep.name");
?>  


<div class="row">
  <div class="col-md-9">
    <?php echo button_tag(TEXT_BUTTON_CREATE,url_for('ext/xml_export/form'),true) ?>  
    <?php echo button_tag(TEXT_EXT_CREATE_GROUP,url_for('ext/xml_export/form_group'),true,['class'=>'btn btn-default']) ?>  
  </div>
  <div class="col-md-3">
    <?php echo form_tag('templates_filter',url_for('ext/xml_export/templates','action=set_xml_templates_filter')) ?>
      <?php echo select_tag('xml_templates_filter',entities::get_choices_with_empty(),$xml_templates_filter,array('class'=>'form-control input-large float-right','onChange'=>'this.form.submit()')) ?>
    </form>
  </div>
</div>  

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th><?php echo TEXT_REPORT_ENTITY ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>    
    <th><?php echo TEXT_ACCESS ?></th> 
    <th><?php echo TEXT_IS_ACTIVE ?></th>   
    <th><?php echo TEXT_SORT_ORDER ?></th>    
  </tr>
</thead>
<tbody>
<?php

if(db_num_rows($templates_query)==0) echo '<tr><td colspan="6">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

$access_groups_cache = access_groups::get_cache();

while($templates = db_fetch_array($templates_query)):
?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/xml_export/delete_confirm','id=' . $templates['id'])) . ' ' . button_icon_edit(url_for('ext/xml_export/' . ($templates['entities_id']>0 ? 'form':'form_group'),'id=' . $templates['id'])) ?></td>
  <td><?php echo $templates['entities_name'] ?></td>
  <td style="white-space: normal"><?php 
    echo $templates['name']; 
    echo ($templates['entities_id']>0 ? '<div><small><a href="' . url_for('ext/xml_export/filters','templates_id=' . $templates['id']) . '">' . TEXT_FILTERS . ' (' . xml_export::count_filters($templates['id']). ')</a></small></div>' : '');  					
    echo ($templates['is_public']==1 ? '<div><small>' . TEXT_URL . ': <a href="' . url_for('export/xml','id=' . $templates['id']) . '" target="_blank">' . url_for('export/xml','id=' . $templates['id']) . '</a></small></div>':'');
    echo xml_export::get_linked_entities_filters_html($templates);    
       
  ?></td>  
  <td>
<?php
  if(strlen($templates['users_groups'])>0)
  {
    $users_groups = array();
    foreach(explode(',',$templates['users_groups']) as $id)
    {
      $users_groups[] = $access_groups_cache[$id];
    }
    
    if(count($users_groups)>0)
    {        
      echo '<span style="display:block" data-html="true" data-toggle="tooltip" data-placement="left" title="' . addslashes(implode(', ',$users_groups)). '">' . TEXT_USERS_GROUPS . ' (' . count($users_groups) . ')</span>';
    }
  }
  
  if($templates['assigned_to']>0)
  {
    $assigned_to = array();
    foreach(explode(',',$templates['assigned_to']) as $id)
    {
      $assigned_to[] = $app_users_cache[$id]['name'];
    }
    
    if(count($assigned_to)>0)
    {        
      echo '<span data-html="true" data-toggle="tooltip" data-placement="left" title="' . addslashes(implode(', ',$assigned_to)). '">' . TEXT_USERS_LIST . ' (' . count($assigned_to) . ')</span>';
    }
  }   
?>  
  </td>
  <td><?php echo render_bool_value($templates['is_active']) ?></td>
  <td><?php echo $templates['sort_order'] ?></td>    
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>