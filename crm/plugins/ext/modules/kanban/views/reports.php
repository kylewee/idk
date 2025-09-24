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

<h3 class="page-title"><?php echo TEXT_EXT_KANBAN ?></h3>

<p><?php echo TEXT_EXT_KANBAN_DESCRIPTION ?></p>

<div class="row">
  <div class="col-md-9">
    <?php echo button_tag(TEXT_BUTTON_CREATE,url_for('ext/kanban/form')) ?>
  </div>
  <div class="col-md-3">
    <?php echo form_tag('reports_filter_form',url_for('ext/kanban/reports','action=set_reports_filter')) ?>
      <?php echo select_tag('reports_filter',entities::get_choices_with_empty(),$kanban_entity_filter,array('class'=>'form-control input-large float-right','onChange'=>'this.form.submit()')) ?>
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
    <th><?php echo TEXT_EXT_GROUP_BY_FIELD ?></th>            
    <th><?php echo TEXT_ACCESS ?></th>
    <th><?php echo TEXT_IS_ACTIVE ?></th>       
  </tr>
</thead>
<tbody>
<?php

$fields_cahce = fields::get_name_cache();

$where_sql = ($kanban_entity_filter>0 ? " where entities_id={$kanban_entity_filter}":'');

$reports_query = db_query("select * from app_ext_kanban {$where_sql} order by name");

if(db_num_rows($reports_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($reports = db_fetch_array($reports_query)):
?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/kanban/delete','id=' . $reports['id'])) . ' ' . button_icon_edit(url_for('ext/kanban/form','id=' . $reports['id'])) ?></td>
  <td><?php echo $app_entities_cache[$reports['entities_id']]['name'] ?></td>
  <td><?php echo $reports['name'] ?>
  
      <?php 
      
      $panels_id = filters_panels::get_id_by_type($reports['entities_id'], 'kanban_reports' . $reports['id']);
      
      echo '<br>&nbsp;&nbsp;<small>' . link_to(TEXT_FILTERS . ' (' . reports::count_filters_by_reports_type($reports['entities_id'], 'default_kanban_reports' . $reports['id']). ') ',url_for('default_filters/filters','reports_id=' . default_filters::get_reports_id($reports['entities_id'], 'default_kanban_reports' . $reports['id']) . '&redirect_to=kanban' . $reports['id']));
      if($reports['filters_panel']=='quick_filters')
      {
        echo '<small>&nbsp;|&nbsp; <a href="' . url_for('ext/filters_panels/fields','panels_id=' . $panels_id . '&entities_id=' . $reports['entities_id'] . '&redirect_to=kanban' . $reports['id']) . '">' . TEXT_QUICK_FILTERS_PANELS . '</a></small></small>';
      }
      
        if(strlen($reports['sum_by_field']))
        {
            foreach(explode(',',$reports['sum_by_field']) as $field_id)
            {
                echo tooltip_text('&nbsp;&nbsp;' . TEXT_EXT_SUM_BY_FIELD . ': ' . $fields_cahce[$field_id]); 
            }	
        }
        
        $fields_in_listing = [];
        if(strlen($reports['fields_in_listing']))
        {
            foreach(explode(',',$reports['fields_in_listing']) as $field_id)
            {
                $fields_in_listing[] = fields::get_name_by_id($field_id);
            }	
            
            echo tooltip_text('&nbsp;&nbsp;' . TEXT_FIELDS_IN_LISTING . ': ' . implode(', ', $fields_in_listing));
        }
      
      ?>
  </td>
    
  <td><?php echo $fields_cahce[$reports['group_by_field']]??'' ?></td>
    
  <td>
      <?php
      if(strlen($reports['users_groups']))
      {	
        $users_groups_list = array();
        foreach(explode(',',$reports['users_groups']) as $users_groups_id)
        {
          $users_groups_list[] = access_groups::get_name_by_id($users_groups_id);
        }
        
        echo '<div>' . implode('<br>',$users_groups_list) . '</div>';
      }
      
    if(strlen($reports['assigned_to']) > 0)
    {
        $assigned_to = array();
        foreach(explode(',', $reports['assigned_to']) as $id)
        {
            $assigned_to[] = users::get_name_by_id($id);
        }

        if(count($assigned_to) > 0)
        {
            echo '<div>' . implode('<br>',$assigned_to) . '</div>';
        }
    }
      ?>
    </td> 
    <td><?php echo render_bool_value($reports['is_active']) ?></td>
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>
