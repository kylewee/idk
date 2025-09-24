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

<?php echo form_tag('configuration_form', url_for('ext/kanban/reports','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
      
    <ul class="nav nav-tabs">
        <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_SETTINGS ?></a></li>
        <li><a href="#access_configuration"  data-toggle="tab"><?php echo TEXT_ACCESS ?></a></li>                       
    </ul>       
     
 
<div class="tab-content">
  <div class="tab-pane fade active in" id="general_info">

    <div class="form-group">
        <label class="col-md-3 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
        <div class="col-md-9">	
            <p class="form-control-static"><?php echo input_checkbox_tag('is_active', $obj['is_active'], array('checked' => ($obj['is_active'] == 1 ? 'checked' : ''))) ?></p>
        </div>			
    </div>
      
    <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
      <div class="col-md-9">	
    	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-large required')) ?>        
      </div>			
    </div>
    
    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="type"><?php echo TEXT_REPORT_ENTITY ?></label>
      <div class="col-md-9">	
    	  <?php echo select_tag('entities_id',entities::get_choices(), $obj['entities_id'],array('class'=>'form-control input-large required','onChange'=>'ext_get_entities_fields(this.value)')) ?>        
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo tooltip_icon(TEXT_HEADING_TEMPLATE_INFO) . TEXT_HEADING_TEMPLATE ?></label>
      <div class="col-md-9">	
    	  <?php echo input_tag('heading_template',$obj['heading_template'],array('class'=>'form-control input-large')) ?>
        <?php echo tooltip_text(TEXT_ENTER_TEXT_PATTERN_INFO) ?>        
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo TEXT_EXT_COLUMN_WIDTH ?></label>
      <div class="col-md-9">	
    	  <?php echo input_tag('width',$obj['width'],array('class'=>'form-control input-medium ')) ?>        
      </div>			
    </div>
      
    <?php
    $choices = [
        '' => '',
        'default' => TEXT_DEFAULT,
        'quick_filters' => TEXT_QUICK_FILTERS_PANELS,
    ];
    ?>
    <div class="form-group">
        <label class="col-md-3 control-label" for="default_view"><?php echo TEXT_FILTERS_PANELS ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('filters_panel', $choices, $obj['filters_panel'], array('class' => 'form-control input-medium')) ?>        
        </div>			
    </div> 
      
    <div class="form-group">
        <label class="col-md-3 control-label" for="name"><?php echo tooltip_icon(TEXT_DEFAULT .': 20') . TEXT_ROWS_PER_PAGE ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('rows_per_page',$obj['rows_per_page'],array('class'=>'form-control input-small','min'=>0,'type'=>'number')) ?>        
        </div>			
    </div>  
        
    
    <div id="reports_entities_fields"></div>  
          
  </div>
    
    <div class="tab-pane fade" id="access_configuration">
        <div class="form-group">
            <label class="col-md-3 control-label" for="users_groups"><?php echo TEXT_USERS_GROUPS ?></label>
            <div class="col-md-9">	
                <?php echo select_tag('users_groups[]', access_groups::get_choices(), $obj['users_groups'], ['class' => 'form-control input-xlarge chosen-select', 'multiple' => 'multiple']) ?>      
            </div>			
        </div> 

        <div class="form-group">
            <label class="col-md-3 control-label" for="users_groups"><?php echo TEXT_ASSIGNED_TO ?></label>
            <div class="col-md-9">	
                <?php
                $attributes = array('class' => 'form-control input-xlarge chosen-select',
                    'multiple' => 'multiple',
                    'data-placeholder' => TEXT_SELECT_SOME_VALUES);

                $assigned_to = (strlen($obj['assigned_to']) > 0 ? explode(',', $obj['assigned_to']) : '');
                echo select_tag('assigned_to[]', users::get_choices(), $assigned_to, $attributes);
                ?>  	        
            </div>			
        </div> 

    </div>

  
     
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    
    $('#configuration_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				return true;
			}
    }); 
                        
    ext_get_entities_fields($('#entities_id').val());
                                                                              
  });
  
function ext_get_entities_fields(entities_id)
{ 
  $('#reports_entities_fields').html('<div class="ajax-loading"></div>');
   
  $('#reports_entities_fields').load('<?php echo url_for("ext/kanban/reports","action=get_entities_fields")?>',{entities_id:entities_id, id:'<?php echo $obj["id"] ?>'},function(response, status, xhr) {
    if (status == "error") {                                 
       $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
    }
    else
    {
    	ext_get_entities_fields_choices()
      appHandleUniform();
      jQuery(window).resize();
    }    
  });      
}   

function ext_get_entities_fields_choices()
{
	var fields_id = $('#group_by_field').val();
	
	if(fields_id>0)
	{
	  $('#fields_chocies_list').html('<div class="ajax-loading"></div>');
   
	  $('#fields_chocies_list').load('<?php echo url_for("ext/kanban/reports","action=get_entities_fields_choices")?>',{fields_id:fields_id, id:'<?php echo $obj["id"] ?>'},function(response, status, xhr) {
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
}
  
</script>   