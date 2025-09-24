
<?php echo ajax_modal_template_header(TEXT_EXT_PROCESS_IFNO) ?>

<?php echo form_tag('process_form', url_for('ext/processes/buttons_groups','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>

<div class="modal-body">
  <div class="form-body">
  
  		<div class="form-group">
		  	<label class="col-md-3 control-label" for="entities_id"><?php echo TEXT_REPORT_ENTITY ?></label>
		    <div class="col-md-9"><?php echo select_tag('entities_id',entities::get_choices(),$obj['entities_id'],array('class'=>'form-control input-large required','onChange'=>'ext_get_entities_users_fields(this.value)')) ?>
		    </div>			
		  </div>
 
		  <div class="form-group">
		  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
		    <div class="col-md-9">	
		  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-large required')) ?>      
		    </div>			
		  </div> 
		  
		  <div class="form-group">
		  	<label class="col-md-3 control-label" for="menu_icon"><?php echo TEXT_ICON; ?></label>
		    <div class="col-md-9">	
		  	  <?php echo input_icon_tag('button_icon', $obj['button_icon'],array('class'=>'form-control input-large')); ?> 		      
		    </div>			
		  </div>
		  
<?php 
	$choices = array();
	$choices['default'] = TEXT_EXT_IN_RECORD_PAGE;
	$choices['menu_with_selected'] = TEXT_EXT_MENU_WITH_SELECTED;
	$choices['in_listing'] = TEXT_EXT_IN_LISTING;
?>		  
		  
		  <div class="form-group">
		  	<label class="col-md-3 control-label" for="menu_icon"><?php echo TEXT_EXT_PROCESS_BUTTON_POSITION; ?></label>
		    <div class="col-md-9">	
		  	  <?php echo select_tag('button_position[]', $choices,$obj['button_position'],array('class'=>'form-control input-xlarge chosen-select required','multiple'=>'multiple')); ?> 		      
		    </div>			
		  </div> 
		  
		  <div class="form-group">
		  	<label class="col-md-3 control-label" for="button_color"><?php echo TEXT_EXT_PROCESS_BUTTON_COLOR ?></label>
		    <div class="col-md-9">
		    	<div class="input-group input-small color colorpicker-default" data-color="<?php echo (strlen($obj['button_color'])>0 ? $obj['button_color']:'#428bca')?>" >
		  	   <?php echo input_tag('button_color',$obj['button_color'],array('class'=>'form-control input-small')) ?>
		        <span class="input-group-btn">
		  				<button class="btn btn-default" type="button">&nbsp;</button>
		  			</span>
		  		</div>		  	  
		    </div>			
		  </div>
		  
		  <div class="form-group">
		  	<label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_SORT_ORDER ?></label>
		    <div class="col-md-9">	
		  	  <?php echo input_tag('sort_order',$obj['sort_order'],array('class'=>'form-control input-xsmall')) ?>
		    </div>			
		  </div>
      
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() {     
    $('#process_form').validate({ignore:'',
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				return true;
			}
    });                                                                
  });    
</script>   
    
 
