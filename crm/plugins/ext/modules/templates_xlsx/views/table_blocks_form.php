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

<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('templates_form', url_for('ext/templates_xlsx/table_blocks','templates_id=' . _GET('templates_id') . '&action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'')  . '&parent_block_id=' . $parent_block['id']),array('class'=>'form-horizontal')) ?>

<div class="modal-body">
  <div class="form-body">
  
<?php

$cfg = new fields_types_cfg($parent_block['field_configuration']);

//print_rr($parent_block);

switch($parent_block['field_type'])
{
    case 'fieldtype_entity':
    case 'fieldtype_entity_ajax':
    case 'fieldtype_entity_multilevel':
    case 'fieldtype_related_records':
        $field_entity_id = $cfg->get('entity_id');
        break;
    default:
        $field_entity_id = 1;
        break;
}

if($parent_block['field_type']=='fieldtype_id' and $app_entities_cache[$parent_block['entities_id']]['parent_id']==$template_info['entities_id'])
{
    $field_entity_id = $parent_block['entities_id'];    
}

$settings = new settings($obj['settings']);

?>  




    
    
<?php 

$choices = [];
$fields_query = fields::get_query($field_entity_id," and f.type not in ('fieldtype_action','fieldtype_parent_item_id') and f.id not in (select fields_id from app_ext_items_export_templates_blocks where block_type = 'body_cell' and templates_id=" . $template_info['id'] . " " . ($obj['fields_id']>0 ? " and fields_id!=" . $obj['fields_id'] :"") . "  and parent_id=" . $parent_block['id'] . ")");
while($fields = db_fetch_array($fields_query))
{
    $choices[$app_entities_cache[$field_entity_id]['name']][$fields['id']] = fields_types::get_option($fields['type'], 'name',$fields['name']);
}

?>    
      <div class="form-group">
      	<label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_FIELD ?></label>
        <div class="col-md-9"><?php echo select_tag('fields_id',$choices,$obj['fields_id'],array('class'=>'form-control input-xlarge chosen-select required')) ?>
        </div>			
      </div>
      
      <div class="form-group">
      	<label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_SORT_ORDER ?></label>
        <div class="col-md-9"><?php echo input_tag('sort_order',$obj['sort_order'],['class'=>'form-control input-xsmall']) ?></div>			
      </div>
            
      <div id="field_settings"></div>
                   
  </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    $('#templates_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				return true;
			}
	  });

    get_field_settings()

    $('#fields_id').change(function(){
    	get_field_settings()
    })
    
  });

  function get_field_settings()
  {
	 $('#field_settings').load("<?php echo url_for('ext/templates_xlsx/table_blocks','id=' . $obj['id'] . '&action=get_field_settings&templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id']) ?>",{fields_id:$('#fields_id').val()},function(){ appHandleUniform(); })  
  }
</script>