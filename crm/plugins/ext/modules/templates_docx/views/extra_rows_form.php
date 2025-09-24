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

<?php echo form_tag('templates_form', url_for('ext/templates_docx/extra_rows','templates_id=' . _GET('templates_id') . '&action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'')  . '&parent_block_id=' . $parent_block['id'] . '&row_id=' . $row_info['id']),array('class'=>'form-horizontal')) ?>

<div class="modal-body ajax-modal-width-790">
  <div class="form-body">
   
<?php $settings = new settings($obj['settings']); ?>

<?php if($row_info['block_type']=='tfoot'){ ?>
<ul class="nav nav-tabs">    
  <li class="active"><a href="#tab_heading"  data-toggle="tab"><?php echo TEXT_HEADING ?></a></li>    
  <li><a href="#tab_value"  data-toggle="tab"><?php echo TEXT_VALUE ?></a></li>
</ul>
<?php } ?>

<div class="tab-content">
  <div class="tab-pane fade active in" id="tab_heading"> 

      <div class="form-group">
      	<label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_HEADING ?></label>
        <div class="col-md-9"><?php echo input_tag('settings[heading]',$settings->get('heading'),['class'=>'form-control input-large']) ?></div>			
      </div>
      
      <div class="form-group">
      	<label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_SORT_ORDER ?></label>
        <div class="col-md-9"><?php echo input_tag('sort_order',$obj['sort_order'],['class'=>'form-control input-xsmall']) ?></div>			
      </div>
      
      <div class="form-group">
      	<label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_MERGE_CELLS . ' (colspan)' ?></label>
        <div class="col-md-9"><?php echo input_tag('settings[colspan]',$settings->get('colspan'),['class'=>'form-control input-xsmall number']) . tooltip_text(TEXT_EXT_MERGE_CELLS_INFO) ?></div>			
      </div>
      
      <div class="form-group settings-list">
        <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_FONT_SIZE ?></label>
        <div class="col-md-9"><?php echo input_tag('settings[heading_font_size]',$settings->get('heading_font_size',''),['class'=>'form-control input-small number']) ?></div>			
      </div>
 
<?php 
$font_style_choices = [
    'bold' => '<i class="fa fa-bold" aria-hidden="true"></i>',
    'italic' => '<i class="fa fa-italic" aria-hidden="true"></i>',
    'underline' => '<i class="fa fa-underline" aria-hidden="true"></i>',
];
?>      
      <div class="form-group settings-list">
        <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_FONT_STYLE ?></label>
        <div class="col-md-9"><?php echo select_checkboxes_button('settings[heading_font_style]',$font_style_choices,$settings->get('heading_font_style','')) ?></div>			
      </div>
      
<?php 
$alignment_choices = [
    'left' => '<i class="fa fa-align-left" aria-hidden="true"></i>',
    'center' => '<i class="fa fa-align-center" aria-hidden="true"></i>',
    'right' => '<i class="fa fa-align-right" aria-hidden="true"></i>',
];
?>      
      <div class="form-group settings-list">
        <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_ALIGNMENT ?></label>
        <div class="col-md-9"><?php echo select_radioboxes_button('settings[heading_alignment]',$alignment_choices,$settings->get('heading_alignment','left')) ?></div>			
      </div> 
      
  </div>
  <div class="tab-pane fade" id="tab_value">
      
<?php
    if($parent_block['extra_type'] == 'table')
    {


    $choices = [                
        'field' => TEXT_FIELD,                
        'php_code' => TEXT_PHP_CODE,            
    ];
    ?>

    <div class="form-group">
        <label class="col-md-3 control-label"><?php echo TEXT_TYPE ?></label>
        <div class="col-md-9"><?php echo select_tag('settings[value_type]', $choices, $settings->get('value_type'), ['class' => 'form-control input-medium']) ?></div>			
    </div>      
<?php          
    }
    else
    {            
        echo input_hidden_tag('settings[value_type]','field');
    }
?>      
       
<?php 
        
    $choices = [];
    $choices[''] = '';
    $fields_query = fields::get_query($template_info['entities_id']);
    while($fields = db_fetch_array($fields_query))
    {
        $choices[$fields['id']] = fields::get_name_by_id($fields['id']); 
    }
?>
      <div class="form-group" form_display_rules="settings_value_type:field">
        <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_FIELD ?></label>
        <div class="col-md-9"><?php echo select_tag('fields_id',$choices,$obj['fields_id'],['class'=>'form-control input-large']) ?></div>			
      </div>
      
      <div id="field_settings"></div>
      
        <div class="form-group settings-list" form_display_rules="settings_value_type:php_code">
            <label class="col-md-3 control-label" for="fields_id"><?= tooltip_icon(TEXT_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT ?></label>
            <div class="col-md-9"><?= input_tag('settings[number_format]',$settings->get('number_format',CFG_APP_NUMBER_FORMAT),['class'=>'form-control input-small input-masked','data-mask'=>'9/~/~']) ?></div>			
          </div>

        <div class="form-group" form_display_rules="settings_value_type:php_code">
            <label class="col-md-3 control-label"><?php echo TEXT_PREFIX ?></label>
            <div class="col-md-9">
                <?php echo input_tag('settings[prefix]', $settings->get('prefix'), ['class' => 'form-control input-small']) ?>                        
            </div>			                                        
        </div>

        <div class="form-group" form_display_rules="settings_value_type:php_code">
            <label class="col-md-3 control-label"><?php echo TEXT_SUFFIX ?></label>
            <div class="col-md-9">
                <?php echo input_tag('settings[suffix]', $settings->get('suffix'), ['class' => 'form-control input-small']) ?>                        
            </div>			                                        
        </div>

        <div class="form-group" form_display_rules="settings_value_type:php_code">
            <label class="col-md-3 control-label"><?php echo TEXT_PHP_CODE . export_templates_blocks::render_total_helper($parent_block['id']); ?></label>
            <div class="col-md-9">
                <?php echo textarea_tag('settings[php_code]', $settings->get('php_code'), ['class'=>($settings->get('value_type')=='php_code' ? 'code_mirror':''),'mode' => 'php']) ?>
                <?php echo tooltip_text(TEXT_EXAMPLE . ': <code>$output_value = $total[\'column_13\'];</code>') ?>
                <?php echo tooltip_text(TEXT_EXAMPLE . ' 2: <code>$output_value = ($item[\'field_13\']==1 ? $total[\'column_13\']:0);</code>'); ?>
            </div>			
        </div>      
                  
  </div>
</div>       
                     
  
  </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<?php echo app_include_codemirror(['javascript','php','clike','css','xml']) ?>          

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
    
    $('#settings_value_type').change(function(){
            if($(this).val()=='php_code')
            {
                $('#settings_php_code').addClass('code_mirror')
                appHandleCodeMirror()
                
                $('#field_settings').html('')
            }
        })
  });

  function get_field_settings()
  {
	 $('#field_settings').load("<?php echo url_for('ext/templates_docx/extra_rows','id=' . $obj['id'] . '&action=get_field_settings&templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id']) ?>",{fields_id:$('#fields_id').val()},function(){ appHandleUniform(); })  
  }
</script>