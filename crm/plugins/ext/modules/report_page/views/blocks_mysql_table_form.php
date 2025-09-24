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

<?php echo form_tag('templates_form', url_for('ext/report_page/blocks_mysql_table', 'report_id=' . _GET('report_id') . '&action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '') . '&block_id=' . $block_info['id']), array('class' => 'form-horizontal')) ?>

<div class="modal-body">
    <div class="form-body">

        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_GENERAL_INFO ?></a></li>
            <li><a href="#tab_mysql_query"  data-toggle="tab"><?php echo TEXT_MYSQL_QUERY ?></a></li>               
        </ul> 

        <div class="tab-content">
            <div class="tab-pane fade active in" id="general_info">

                <?php
                $settings = new settings($obj['settings']);
                ?>  

                <div class="form-group">
                    <label class="col-md-3 control-label"><?php echo TEXT_HEADING ?></label>
                    <div class="col-md-9"><?php echo input_tag('settings[heading]', $settings->get('heading'), ['class' => 'form-control input-large']) ?></div>			
                </div>                                

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
                    <div class="col-md-9"><?= input_tag('settings[number_format]',$settings->get('number_format',CFG_APP_NUMBER_FORMAT),['class'=>'form-control input-small input-masked','data-mask'=>'9/~/~']) ?></div>			
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
                        <?php echo input_tag('settings[date_format]', $settings->get('date_format','Y-m-d'), ['class' => 'form-control input-small']) ?>                        
                        <?= TEXT_DATE_FORMAT_IFNO ?>
                    </div>			                                        
                </div>

                <div class="form-group settings-list">
                    <label class="col-md-3 control-label"><?php echo sprintf(TEXT_EXT_TAG_X_ATTRIBUTES, 'TD') ?></label>
                    <div class="col-md-9">
                        <?php echo input_tag('settings[tag_td_attributes]', $settings->get('tag_td_attributes', ''), ['class' => 'form-control input-xlarge']) ?>
                        <?php echo tooltip_text(TEXT_EXAMPLE . ': <code>style="width:100px;"</code>') ?>
                    </div>			
                </div>        


                <?php
                $html =  TEXT_EXAMPLE . ' 1:  <code>$output_value = $item[\'field_13\']*0.2;</code><br>';
                $html .= TEXT_EXAMPLE . ' 2:  <code>$output_value = $app_choices_cache[$item[\'field_13\']][\'name\'];</code><br>';
                $html .= TEXT_EXAMPLE . ' 3:  <code>$output_value = $app_global_choices_cache[$item[\'field_13\']][\'name\'];</code><br>';
                $html .= TEXT_EXAMPLE . ' 4:  <code>$output_value = $app_users_cache[$item[\'field_13\']][\'name\'];</code><br>';
                ?>

                <div class="form-group" form_display_rules="settings_value_type:php_code">
                    <label class="col-md-3 control-label"><?php echo TEXT_PHP_CODE ?></label>
                    <div class="col-md-9">
                        <?php echo textarea_tag('settings[php_code]', $settings->get('php_code'), ['class' => ($settings->get('value_type') == 'php_code' ? 'code_mirror' : ''), 'mode' => 'php']) ?>
                        <?php echo tooltip_text($html) ?>
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?php echo TEXT_SORT_ORDER ?></label>
                    <div class="col-md-9"><?php echo input_tag('sort_order', $obj['sort_order'], ['class' => 'form-control input-xsmall']) ?></div>			
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