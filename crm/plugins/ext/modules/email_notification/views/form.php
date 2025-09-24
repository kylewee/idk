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

<?php echo form_tag('configuration_form', url_for('ext/email_notification/rules', 'action=save&entities_id=' . _get::int('entities_id') . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '')), array('class' => 'form-horizontal')) ?>
<?php echo input_hidden_tag('entities_id', _get::int('entities_id')) ?>
<div class="modal-body">
    <div class="form-body">

        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_GENERAL_INFO ?></a></li>
            <li><a href="#listing_sfg"  data-toggle="tab"><?php echo TEXT_NAV_LISTING_CONFIG ?></a></li>
            <li><a href="#message_text"  data-toggle="tab"><?php echo TEXT_EXT_MESSAGE_TEXT ?></a></li>
            <li><a href="#message_note"  data-toggle="tab"><?php echo TEXT_NOTE ?></a></li>	  
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade active in" id="general_info">

                <div class="form-group">
                    <label class="col-md-3 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
                    <div class="col-md-9">	
                        <p class="form-control-static"><?php echo input_checkbox_tag('is_active', 1, array('checked' => $obj['is_active'])) ?></p>      
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="type"><?php echo TEXT_EXT_RULE ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('action_type', email_notification_rules::get_action_type_choices(), $obj['action_type'], array('class' => 'form-control required', 'onChange' => 'ext_get_entities_fields()')) ?>        
                    </div>			
                </div>
                
                <div id="rules_entities_fields"></div> 
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="name"><?php echo TEXT_DAY ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('notification_days[]',app_get_days_choices(),$obj['notification_days'],array('multiple'=>'multiple','class'=>'form-control chosen-select')) ?>
                        <?php echo tooltip_text(TEXT_DEFAULT . ': ' . TEXT_EVERY_DAY) ?>
                    </div>			
                </div>

                <div class="form-group">
                     <label class="col-md-3 control-label" for="name"><?php echo TEXT_TIME ?></label>
                    <div class="col-md-9">	
                          <?php echo select_tag('notification_time[]',app_get_hours_choices(),$obj['notification_time'],array('multiple'=>'multiple','class'=>'form-control input-medium chosen-select required')) ?>
                    </div>			
                </div>
                      
                <div class="form-group">
                    <label class="col-md-3 control-label" for="cfg_sms_send_to_number_text"><?php echo  TEXT_EMAIL_SUBJECT; ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('subject', $obj['subject'], array('class' => 'form-control input-xlarge textarea-small required')); ?>
                        <?= tooltip_text(TEXT_YOU_CAN_USE . ': ${current_date}, ${current_date_time}' ) ?>
                    </div>			
                </div> 

            </div>
            
            <div class="tab-pane fade" id="listing_sfg">
                <div class="form-group">
                    <label class="col-md-3 control-label" for="type"><?php echo TEXT_TYPE ?></label>
                    <div class="col-md-9">	
                        <?php 
                        $choices = [
                            'list' => TEXT_LIST,
                            'table' => TEXT_TABLE,
                        ];
                        echo select_tag('listing_type', $choices, $obj['listing_type'], array('class' => 'form-control input-medium', 'onChange' => 'email_listing_type_cfg()')) ?>        
                    </div>			
                </div>
                
                <div id="listing_settings"></div>
            </div>
            
            <div class="tab-pane fade" id="message_text">

                <div class="form-group">                                                           
                    <div class="col-md-12">	
                        <?php echo textarea_tag('description', $obj['description'], array('class' => 'form-control input-xlarge full-editor', 'editor-height' => 350)); ?>	  	        
                    </div>			
                </div> 
                            
            </div>

            <div class="tab-pane fade" id="message_note">
                <div class="form-group">
                    <label class="col-md-3 control-label" for="type"><?php echo TEXT_ADMINISTRATOR_NOTE ?></label>
                    <div class="col-md-9">	
                        <?php echo textarea_tag('notes',$obj['notes'],array('class'=>'form-control')) ?>
                    </div>			
                </div>
            </div>

        </div>


    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>

    $(function ()
    {
        $('#configuration_form').validate({ignore: '',
            submitHandler: function (form)
            {
                app_prepare_modal_action_loading(form)
                return true;
            },
            invalidHandler: function(e, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) 
                {                                   
                    app_highlight_form_tab_name_with_errors('configuration_form')                                                                                			
                }                 
            }
        });

        ext_get_entities_fields();
        
        email_listing_type_cfg()
        
    });


    function email_listing_type_cfg()
    {
        $('#listing_settings').html('<div class="ajax-loading"></div>');

        $('#listing_settings').load('<?php echo url_for("ext/email_notification/rules", "action=listing_sfg") ?>', {listing_type: $('#listing_type').val(), entities_id: $('#entities_id').val(), id: '<?php echo $obj["id"] ?>'}, function (response, status, xhr)
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

    function ext_get_entities_fields()
    {
        var entities_id = $('#entities_id').val();
        var action_type = $('#action_type').val();

        $('#rules_entities_fields').html('<div class="ajax-loading"></div>');

        $('#rules_entities_fields').load('<?php echo url_for("ext/email_notification/rules", "action=get_entities_fields") ?>', {action_type: action_type, entities_id: entities_id, id: '<?php echo $obj["id"] ?>'}, function (response, status, xhr)
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