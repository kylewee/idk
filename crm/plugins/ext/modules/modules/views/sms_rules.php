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

<h3 class="page-title"><?php echo TEXT_EXT_SMS_SENDIGN_RULES ?></h3>

<p><?php echo TEXT_EXT_SMS_SENDIGN_RULES_INFO ?></p>

<div class="row">
  <div class="col-md-9">
    <?php echo button_tag(TEXT_BUTTON_CREATE,url_for('ext/modules/sms_rules_form'),true) ?>
  </div>
  <div class="col-md-3">
    <?php echo form_tag('entity_filter',url_for('ext/modules/sms_rules','action=set_modules_entity_filter')) ?>
      <?php echo select_tag('modules_entity_filter',entities::get_choices_with_empty(),$modules_entity_filter,array('class'=>'form-control input-large float-right','onChange'=>'this.form.submit()')) ?>
    </form>
  </div>
</div> 

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th><?php echo TEXT_ID ?></th>        
    <th><?php echo TEXT_EXT_SMS_MODULE ?></th>
    <th><?php echo TEXT_REPORT_ENTITY ?></th>
    <th><?php echo TEXT_TYPE ?></th>
    <th><?php echo TEXT_EXT_RULE ?></th>        
    <th><?php echo TEXT_EXT_SEND_TO_NUMBER ?></th>
    <th width="100%"><?php echo TEXT_EXT_MESSAGE_TEXT ?></th>     
    <th><?php echo TEXT_IS_ACTIVE ?></th>
  </tr>
</thead>
<tbody>
<?php

$modules = new modules('sms');

$where_sql = '';
if($modules_entity_filter>0)
{
    $where_sql = " and e.id={$modules_entity_filter}";
}

$rules_query = db_query("select r.*, e.name as entity_name, m.module from app_ext_sms_rules r left join app_ext_modules m on m.id=r.modules_id, app_entities e where e.id=r.entities_id {$where_sql} order by e.name, r.action_type");

$fields_cahce = fields::get_name_cache();

if(db_num_rows($rules_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($rules = db_fetch_array($rules_query)):

$module_title = '';
if(strlen($rules['module']))
{	
	$module = new $rules['module'];
	$module_title = $module->title;
}

?>
<tr>
  <td style="white-space: nowrap;"><?php 
    echo button_icon_delete(url_for('ext/modules/sms_rules_delete','id=' . $rules['id'])) . ' ' . button_icon_edit(url_for('ext/modules/sms_rules_form','id=' . $rules['id']))  . ' ' . button_icon(TEXT_COPY, 'fa fa-files-o', url_for('ext/modules/sms_rules', 'action=copy&id=' . $rules['id']), false, ['onClick' => 'return confirm("' . addslashes(TEXT_COPY_RECORD) . '?")']);
  ?></td>
  <td><?php echo $rules['id'] ?></td>
  <td><?php echo $module_title ?></td>
  <td><?php echo $rules['entity_name'] ?></td>
  <td><?php echo sms::get_action_type($rules['action_type']) ?></td>
  <td><?php 
    echo sms::get_action_type_name($rules['action_type']) . ' ' . tooltip_icon($rules['notes']) . ($rules['monitor_fields_id']>0 ? '<br><i>' . TEXT_EXT_PB_NOTIFY_FIELD_CHANGE . ':</i> <span class="label label-warning">' . $fields_cahce[$rules['monitor_fields_id']]  . '</span>': '');
    echo '<br>' . link_to(TEXT_FILTERS . ' (' . reports::count_filters_by_reports_type($rules['entities_id'], 'sms_rules' . $rules['id']). ') ',url_for('default_filters/filters','reports_id=' . default_filters::get_reports_id($rules['entities_id'], 'sms_rules' . $rules['id']) . '&redirect_to=sms_rules' . $rules['id']),['title'=>TEXT_SET_MSG_FILTERS]);
        ?></td>  
  <td><?php 
      if(in_array($rules['action_type'],['insert_send_to_number_in_entity','edit_send_to_number_in_entity']))
      {
          $value = explode(':',$rules['phone']);
          
          $fields_query = db_query("select configuration from app_fields where id='" . $value[0] . "'");
          if($fields = db_fetch_array($fields_query))
          {   
              $cfg = new settings($fields['configuration']);
              echo entities::get_name_by_id($cfg->get('entity_id')) . ': ' . fields::get_name_by_id($value[1]);
          }
      }
      else
      {
          echo ($rules['fields_id']>0 ? TEXT_FIELD . ': ' . $fields_cahce[$rules['fields_id']] : $rules['phone']);
      }
      
   ?></td>    
  <td class="white-space-normal"><?php echo nl2br($rules['description']) ?></td>
  <td><?php echo render_bool_value($rules['is_active'], true) ?></td>
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>