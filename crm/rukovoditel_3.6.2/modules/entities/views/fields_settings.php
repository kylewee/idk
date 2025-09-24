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

<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo TEXT_FIELD_SETTINGS . ': ' . $fields_info['name'] ?></h3>

<?php echo form_tag('fields_form', url_for('entities/fields_settings','action=save&fields_id=' . $_GET['fields_id'] . '&entities_id=' . $_GET['entities_id'])) ?>

<?php
  //defautl field configuration
  $cfg = fields_types::parse_configuration($fields_info['configuration']);
  
  $exclude_cfg_keys = array();
   
  //get field configuraiton by type
  switch($fields_info['type'])
  {
    case 'fieldtype_related_records':
        $exclude_cfg_keys = array('fields_in_listing','fields_in_popup');
        
        require(component_path('entities/fieldtype_related_records_settings'));
      break;
      
    case 'fieldtype_entity':
        $exclude_cfg_keys = array('fields_in_popup');
        
        require(component_path('entities/fieldtype_entity_settings'));
      break;
  }
    
  //prepare other configuration if exist
  foreach($cfg as $k=>$v)
  {
    if(!in_array($k,$exclude_cfg_keys))
    {
    	if(is_array($v))
    	{
    		foreach($v as $vv)
    		{
    			echo input_hidden_tag('fields_configuration[' . $k . '][]',$vv);
    		}
    	}
    	else 
    	{
      	echo input_hidden_tag('fields_configuration[' . $k . ']',$v);
    	}
    } 
  } 
?>

<br>
<?php echo submit_tag(TEXT_BUTTON_SAVE) ?>

</form>





