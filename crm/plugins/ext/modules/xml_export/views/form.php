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

<?php echo form_tag('templates_form', url_for('ext/xml_export/templates', 'action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '')), array('class' => 'form-horizontal')) ?>

<div class="modal-body">
    <div class="form-body ajax-modal-width-1100">


        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_GENERAL_INFO ?></a></li>
            <li><a href="#access_configuration"  data-toggle="tab"><?php echo TEXT_ACCESS ?></a></li>   
            <li><a href="#template_configuration"  data-toggle="tab"><?php echo TEXT_EXT_TEMPLATE ?></a></li>    
            <li><a href="#related_entities"  data-toggle="tab"><?php echo TEXT_LINKED_ENTITIES ?></a></li>    
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
                    <label class="col-md-3 control-label" for="entities_id"><?php echo TEXT_ENTITY ?></label>
                    <div class="col-md-9"><?php echo select_tag('entities_id', entities::get_choices(), $obj['entities_id'], array('class' => 'form-control input-large required')) ?>
                    </div>			
                </div>  

                <div class="form-group">
                    <label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('name', $obj['name'], array('class' => 'form-control input-large required')) ?>
                    </div>			
                </div>  

                <div class="form-group">
                    <label class="col-md-3 control-label" for="name"><?php echo tooltip_icon(TEXT_ENTER_TEXT_PATTERN_INFO) . TEXT_FILENAME ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('template_filename', $obj['template_filename'], array('class' => 'form-control input-large')) ?>
                        <?php echo tooltip_text('<label>' . input_checkbox_tag('transliterate_filename', '1', ['checked' => $obj['transliterate_filename']]) . ' ' . TEXT_EXT_TRANSLITERATE_FILENAME . '</label>'); ?>	  	  
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="button_title"><?php echo TEXT_EXT_PROCESS_BUTTON_TITLE; ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('button_title', $obj['button_title'], array('class' => 'form-control input-medium')); ?> 
                    </div>			
                </div> 

                <div class="form-group">
                    <label class="col-md-3 control-label" for="button_position"><?php echo TEXT_EXT_PROCESS_BUTTON_POSITION; ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('button_position[]', xml_export::get_position_choices(), $obj['button_position'], array('class' => 'form-control input-xlarge chosen-select required', 'multiple' => 'multiple')); ?> 
                    </div>			
                </div> 

                <div class="form-group">
                    <label class="col-md-3 control-label" for="menu_icon"><?php echo TEXT_ICON; ?></label>
                    <div class="col-md-9">	
                        <?php echo input_icon_tag('button_icon', $obj['button_icon'], array('class' => 'form-control input-large')); ?>                         
                    </div>			
                </div> 

                <div class="form-group">
                    <label class="col-md-3 control-label" for="button_color"><?php echo TEXT_EXT_PROCESS_BUTTON_COLOR ?></label>
                    <div class="col-md-9">
                        <div class="input-group input-small color colorpicker-default" data-color="<?php echo (strlen($obj['button_color']) > 0 ? $obj['button_color'] : '#428bca') ?>" >
                            <?php echo input_tag('button_color', $obj['button_color'], array('class' => 'form-control input-small')) ?>
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button">&nbsp;</button>
                            </span>
                        </div>		  	  
                    </div>			
                </div>


                <div class="form-group">
                    <label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_SORT_ORDER ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('sort_order', $obj['sort_order'], array('class' => 'form-control input-xsmall')) ?>
                    </div>			
                </div>  

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

                <div class="form-group">
                    <label class="col-md-3 control-label" for="users_groups"><?php echo TEXT_ALLOW_PUBLIC_ACCESS ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('is_public', app_get_boolean_choices(), $obj['is_public'], ['class' => 'form-control input-small']) ?>
                        <?php echo tooltip_text(TEXT_EXT_XML_EXPORT_PUBLIC_ACCESS_TIP) ?>      
                    </div>			
                </div> 

            </div>

            <div class="tab-pane fade" id="template_configuration">

                <div id="template_fields"></div>


            </div>


            <div class="tab-pane fade" id="related_entities">

                <div id="related_entities_body"></div>


            </div>
        </div>  

    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>

</form> 



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

        load_xml_template_fields();
        
        load_xml_related_entities();

        $('#entities_id').change(function ()
        {
            load_xml_template_fields();
            load_xml_related_entities()
        })

    });

    function load_xml_template_fields()
    {

        $('#template_fields').html('<div class="ajax-loading"></div>');

        $('#template_fields').load('<?php echo url_for("ext/xml_export/templates", "action=get_fields") ?>', {entities_id: $('#entities_id').val(), id: '<?php echo $obj["id"] ?>'}, function (response, status, xhr)
        {
            if (status == "error")
            {
                $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
            }
            else
            {
                appHandleUniform();
                jQuery(window).resize();
            }
        });
    }
    
    function load_xml_related_entities()
    {

        $('#related_entities_body').html('<div class="ajax-loading"></div>');

        $('#related_entities_body').load('<?php echo url_for("ext/xml_export/templates", "action=get_related_entities") ?>', {entities_id: $('#entities_id').val(), id: '<?php echo $obj["id"] ?>'}, function (response, status, xhr)
        {
            if (status == "error")
            {
                $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
            }
            else
            {
                appHandleUniform();
                jQuery(window).resize();
            }
        });
    }
</script>  