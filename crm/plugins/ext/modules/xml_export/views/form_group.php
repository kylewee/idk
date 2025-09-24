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

<?php echo ajax_modal_template_header(TEXT_EXT_HEADING_TEMPLATE_IFNO) ?>

<?php echo form_tag('templates_form', url_for('ext/xml_export/templates','action=save_group' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>

<div class="modal-body">
  <div class="form-body">
  
  <p><?php echo TEXT_EXT_COMBINE_TEMPLATES_INFO ?></p>

  <div class="form-group">
  	<label class="col-md-3 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo input_checkbox_tag('is_active',$obj['is_active'],array('checked'=>($obj['is_active']==1 ? 'checked':''))) ?></p>
    </div>			
  </div>
    
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-xlarge required')) ?>
    </div>			
  </div>  
  
     
        
  <div class="form-group">
  	<label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_SORT_ORDER ?></label>
    <div class="col-md-9">	
  	  <?php echo input_tag('sort_order',$obj['sort_order'],array('class'=>'form-control input-xsmall')) ?>
    </div>			
  </div>  
  
<?php

    $choices = [];
    $templates_query = db_query("select * from app_ext_xml_export_templates where entities_id>0 order by sort_order, name");
    while($templates = db_fetch_array($templates_query))
    {
       $choices[$templates['id']] =  $templates['name'];
    }

    $html = '
        <div class="form-group">
            <label class="col-md-3 control-label" for="users_groups">' . TEXT_START . '</label>
            <div class="col-md-9">	
                  ' . textarea_tag('template_header',$obj['template_header'],['class'=>'form-control textarea-small code','style'=>'font-size:13px;']) . '
                  ' . tooltip_text(TEXT_EXT_XML_EXPORT_START_TIP) . '
            </div>			
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label" for="users_groups">' . TEXT_EXT_TEMPLATES . '</label>
            <div class="col-md-9">
                ' . select_tag('template_body[]',$choices,$obj['template_body'],['class'=>'form-control chosen-select required','multiple'=>'multiple']) . '
            </div>			
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label" for="users_groups">' . TEXT_END . '</label>
            <div class="col-md-9">	
                  ' . textarea_tag('template_footer',$obj['template_footer'],['class'=>'form-control textarea-small code','style'=>'font-size:13px;']) . '      
            </div>			
        </div>
        ';
    
    echo $html;
?>
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 



<script>
  $(function() { 
    $('#templates_form').validate({ignore:'',
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				return true;
			}
	  });                                                                
  });  


</script>  