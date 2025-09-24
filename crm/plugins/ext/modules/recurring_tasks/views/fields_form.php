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

<?php echo ajax_modal_template_header(TEXT_HEADING_FIELD_IFNO) ?>

<?php echo form_tag('entities_templates_fields', url_for('ext/recurring_tasks/fields','action=save&tasks_id=' . _get::int('tasks_id'). '&path=' . $app_path .  (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>

<div class="modal-body">
  <div class="form-body ajax-modal-width-790">
 
	  <div class="form-group">
	  	<label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_SELECT_FIELD ?></label>
	    <div class="col-md-9">	
	  	  <?php echo select_tag('fields_id',recurring_tasks::get_actions_fields_choices($current_entity_id),$obj['fields_id'],array('class'=>'form-control input-large required','onChange'=>'render_template_field(this.value)')) ?>
	    </div>			
	  </div>
	     
	  <div class="form-group">
	  	<label class="col-md-3 control-label" for="fields_id"></label>
	    <div class="col-md-9">	
	  	  <div id="template_field_container"></div>
	    </div>			
	  </div>
	  
       
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>


  $(function() { 
    $('#entities_templates_fields').validate();
    
    render_template_field($('#fields_id').val());
    
    check_enter_manually();

    $('#enter_manually').change(function(){
    	check_enter_manually();
    })                                                                      
  });

function check_enter_manually()
{
	if($('#enter_manually').val()==1)
	{
		$('#template_field_container').hide();
	}
	else
	{
		$('#template_field_container').show();
	}
}
  
function render_template_field(fields_id)
{
  $('#template_field_container').html('<div class="ajax-loading"></div>');
  
  $('#template_field_container').load('<?php echo url_for("ext/recurring_tasks/fields","tasks_id=" . _get::int('tasks_id') . "&path=" . $app_path . "&action=render_template_field")?>',{fields_id:fields_id, id:'<?php echo $obj["id"] ?>'},function(response, status, xhr) {
    if (status == "error") {                                 
       $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
    }
    else
    {   
      appHandleUniform();
    }
  });
}  
  
</script>  