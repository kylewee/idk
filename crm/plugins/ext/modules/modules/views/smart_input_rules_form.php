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

<?php echo form_tag('configuration_form', url_for('ext/modules/smart_input_rules','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body ajax-modal-width-790">

<?php 
$modules = new modules('smart_input');
$modules_choices = $modules->get_active_modules();
?>    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="type"><?php echo TEXT_EXT_MODULE ?></label>
      <div class="col-md-9">	
    	  <?php echo select_tag('modules_id',$modules_choices, $obj['modules_id'],array('class'=>'form-control input-large required','onChange'=>'ext_get_entities_fields()')) ?>        
      </div>			
    </div>
         
    <div class="form-group">
    	<label class="col-md-3 control-label" for="type"><?php echo TEXT_ENTITY ?></label>
      <div class="col-md-9">	
    	  <?php echo select_tag('entities_id',entities::get_choices(), $obj['entities_id'],array('class'=>'form-control input-large required','onChange'=>'ext_get_entities_fields()')) ?>        
      </div>			
    </div>
                
    <div id="rules_entities_fields"></div> 

           
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>

  $(function() {     
    $('#configuration_form').validate({ignore:'',
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    }); 
                        
    ext_get_entities_fields($('#entities_id').val());                                                                              
  });
  
function ext_get_entities_fields()
{ 
	var entities_id = $('#entities_id').val();
	var modules_id = $('#modules_id').val();
	
  $('#rules_entities_fields').html('<div class="ajax-loading"></div>');
   
  $('#rules_entities_fields').load('<?php echo url_for("ext/modules/smart_input_rules","action=get_entities_fields")?>',{entities_id:entities_id,modules_id:modules_id, id:'<?php echo $obj["id"] ?>'},function(response, status, xhr) {
    if (status == "error") {                                 
       $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
    }
    else
    {
      appHandleUniform();
      jQuery(window).resize();
    }    
  });      
}    
</script>   