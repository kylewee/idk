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

<?php
$font_style_choices = [
    'bold' => '<i class="fa fa-bold" aria-hidden="true"></i>',
    'italic' => '<i class="fa fa-italic" aria-hidden="true"></i>',
    'underline' => '<i class="fa fa-underline" aria-hidden="true"></i>',
];

$alignment_choices = [
    'left' => '<i class="fa fa-align-left" aria-hidden="true"></i>',
    'center' => '<i class="fa fa-align-center" aria-hidden="true"></i>',
    'right' => '<i class="fa fa-align-right" aria-hidden="true"></i>',
];
?> 


<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('templates_form', url_for('ext/templates_docx/blocks_mysql_table', 'templates_id=' . _GET('templates_id') . '&action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '') . '&parent_block_id=' . $parent_block['id']), array('class' => 'form-horizontal')) ?>

<div class="modal-body ajax-modal-width-1100">
    <div class="form-body">

        <?php
        $settings = new settings($obj['settings']);
        
        $block_settings = new settings($parent_block['settings'])
        ?>  

        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_VALUE ?></a></li>   
            <li><a href="#tab_heading"  data-toggle="tab"><?php echo TEXT_HEADING ?></a></li> 
            <li><a href="#tab_mysql_query"  data-toggle="tab"><?php echo TEXT_MYSQL_QUERY ?></a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade active in" id="general_info">

<?php
$choices = [
    'text' => TEXT_TEXT,
    'numeric' => TEXT_NUMBER,
    'date' => TEXT_DATE,
    'php_code' => TEXT_PHP_CODE,
    'empty' => TEXT_EMPTY_VALUE,
];
?>        
                <div class="form-group">
                    <label class="col-md-3 control-label"><?php echo TEXT_TYPE ?></label>
                    <div class="col-md-9"><?php echo select_tag('settings[value_type]', $choices, $settings->get('value_type'), ['class' => 'form-control input-medium']) ?></div>			
                </div>
                <div class="form-group" form_display_rules="settings_value_type:text,numeric,date">
                    <label class="col-md-3 control-label"><?php echo tooltip_icon(TEXT_VALUE_FROM_SQL_TIP) . TEXT_VALUE ?></label>
                    <div class="col-md-9">
                        <?php echo input_tag('settings[column]', $settings->get('column'), ['class' => 'form-control input-large']) ?>
                        <?= tooltip_text(TEXT_EXAMPLE . ': <code>total</code>') ?>
                    </div>			                                        
                </div>

                <div class="form-group settings-list" form_display_rules="settings_value_type:numeric,php_code">
                    <label class="col-md-3 control-label" for="fields_id"><?= tooltip_icon(TEXT_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT ?></label>
                    <div class="col-md-9"><?= input_tag('settings[number_format]', $settings->get('number_format', CFG_APP_NUMBER_FORMAT), ['class' => 'form-control input-small input-masked', 'data-mask' => '9/~/~']) ?></div>			
                </div>

                <div class="form-group" form_display_rules="settings_value_type:numeric,php_code">
                    <label class="col-md-3 control-label"><?php echo TEXT_PREFIX ?></label>
                    <div class="col-md-9">
                        <?php echo input_tag('settings[prefix]', $settings->get('prefix'), ['class' => 'form-control input-small']) ?>                        
                    </div>			                                        
                </div>

                <div class="form-group" form_display_rules="settings_value_type:numeric,php_code">
                    <label class="col-md-3 control-label"><?php echo TEXT_SUFFIX ?></label>
                    <div class="col-md-9">
                        <?php echo input_tag('settings[suffix]', $settings->get('suffix'), ['class' => 'form-control input-small']) ?>                        
                    </div>			                                        
                </div>

                <div class="form-group" form_display_rules="settings_value_type:date">
                    <label class="col-md-3 control-label"><?php echo TEXT_DATE_FORMAT ?></label>
                    <div class="col-md-9">
                        <?php echo input_tag('settings[date_format]', $settings->get('date_format', 'Y-m-d'), ['class' => 'form-control input-small']) ?>                        
                        <?= TEXT_DATE_FORMAT_IFNO ?>
                    </div>			                                        
                </div>
                
                <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_FONT_SIZE ?></label>
                    <div class="col-md-9"><?php echo input_tag('settings[content_font_size]', $settings->get('content_font_size', ''), ['class' => 'form-control input-small number']) ?></div>			
                </div>

                <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_FONT_STYLE ?></label>
                    <div class="col-md-9"><?php echo select_checkboxes_button('settings[content_font_style]', $font_style_choices, $settings->get('content_font_style', '')) ?></div>			
                </div>

                <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_ALIGNMENT ?></label>
                    <div class="col-md-9"><?php echo select_radioboxes_button('settings[content_alignment]', $alignment_choices, $settings->get('content_alignment', 'left')) ?></div>			
                </div>  
                
                
<?php
$html = TEXT_EXAMPLE . ' 1:  <code>$output_value = $item[\'field_13\']*0.2;</code><br>';
$html .= TEXT_EXAMPLE . ' 2:  <code>$output_value = $app_choices_cache[$item[\'field_13\']][\'name\'];</code><br>';
$html .= TEXT_EXAMPLE . ' 3:  <code>$output_value = $app_global_choices_cache[$item[\'field_13\']][\'name\'];</code><br>';
$html .= TEXT_EXAMPLE . ' 4:  <code>$output_value = $app_user[$item[\'field_13\']][\'name\'];</code><br>';
?>

                <div class="form-group" form_display_rules="settings_value_type:php_code">
                    <label class="col-md-3 control-label"><?php echo TEXT_PHP_CODE ?></label>
                    <div class="col-md-9">
                    <?php echo textarea_tag('settings[php_code]', $settings->get('php_code'), ['class' => ($settings->get('value_type') == 'php_code' ? 'code_mirror' : ''), 'mode' => 'php']) ?>
                    <?php echo tooltip_text($html) ?>
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_SORT_ORDER ?></label>
                    <div class="col-md-9"><?php echo input_tag('sort_order', $obj['sort_order'], ['class' => 'form-control input-xsmall']) ?></div>			
                </div>

            </div>

            <div class="tab-pane fade" id="tab_heading">
                <div class="form-group">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_HEADING ?></label>
                    <div class="col-md-9"><?php echo input_tag('settings[heading]', $settings->get('heading'), ['class' => 'form-control input-large']) ?></div>			
                </div>

                <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_WIDHT ?></label>
                    <div class="col-md-9"><?php echo input_tag('settings[cell_width]', $settings->get('cell_width', ''), ['class' => 'form-control input-small number']) ?></div>			
                </div>

                <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_FONT_SIZE ?></label>
                    <div class="col-md-9"><?php echo input_tag('settings[heading_font_size]', $settings->get('heading_font_size', ''), ['class' => 'form-control input-small number']) ?></div>			
                </div>

     
                <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_FONT_STYLE ?></label>
                    <div class="col-md-9"><?php echo select_checkboxes_button('settings[heading_font_style]', $font_style_choices, $settings->get('heading_font_style', '')) ?></div>			
                </div>

     
                <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_ALIGNMENT ?></label>
                    <div class="col-md-9"><?php echo select_radioboxes_button('settings[heading_alignment]', $alignment_choices, $settings->get('heading_alignment', 'left')) ?></div>			
                </div>  

<?php
$direction_choices = [
    '' => '<i class="fa fa-long-arrow-right" aria-hidden="true"></i>',
    'BTLR' => '<i class="fa fa-long-arrow-up" aria-hidden="true"></i>',
    'TBRL' => '<i class="fa fa-long-arrow-down" aria-hidden="true"></i>',
];
?>      
                <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_EXT_TEXT_DIRECTION ?></label>
                    <div class="col-md-9"><?php echo select_radioboxes_button('settings[heading_text_direction]', $direction_choices, $settings->get('heading_text_direction', '')) ?></div>			
                </div>

            </div>
            
            <div class="tab-pane fade" id="tab_mysql_query">   
                <?= textarea_tag('mysql_query',$block_settings->get('mysql_query'),['class'=>'form-control','readonly'=>'readonly','style'=>'height:300px']) ?>
            </div>

        </div> 


    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>

</form> 

<?php echo app_include_codemirror(['javascript', 'php', 'clike', 'css', 'xml']) ?>          

<script>
    $(function ()
    {
        $('#templates_form').validate({
            submitHandler: function (form)
            {
                app_prepare_modal_action_loading(form)
                return true;
            }
        });


        $('#settings_value_type').change(function ()
        {
            if ($(this).val() == 'php_code')
            {
                $('#settings_php_code').addClass('code_mirror')
                appHandleCodeMirror()
            }
        })

    });

</script>