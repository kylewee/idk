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

<?php echo ajax_modal_template_header(TEXT_REPLY) ?>

<?php echo form_tag('mail_form', url_for('ext/mail/reply', 'action=send&mail_id=' . $email_info['id']), array('class' => 'form-horizontal')) ?>
<div class="modal-body">
    <div class="form-body ajax-modal-width-790">

        <div class="form-group">
            <label class="col-md-2 control-label" for="is_active"><?php echo TEXT_EXT_EMAIL_FROM ?></label>
            <div class="col-md-10">	
                <?php echo select_tag('accounts_id', mail_accounts::get_choices_by_user(), $email_info['accounts_id'], array('class' => 'form-control')) ?>
            </div>			
        </div>
        
<?php
    $choices = mail_accounts::get_signature_choices();
    foreach($choices as $id=>$text)
    {
        echo input_hidden_tag('signature[' . $id . ']',$text);
    }    
?>        

        <div class="form-group">
            <label class="col-md-2 control-label" for="is_active"><?php echo TEXT_EXT_EMAIL_TO ?></label>
            <div class="col-md-10">	
                <p class="form-control-static mail-list-box">	    
                    <?php
                    $choices = [];
                    
                    if ($email_info['is_sent'] != 1)                    
                    {
                        $email = (strlen($email_info['from_name']) ? $email_info['from_name'] . ' <' . $email_info['from_email'] . '>' : $email_info['from_email']);
                        $choices[$email] = $email;                                                                                                 
                    }
                    
                    $to_name = explode(',', $email_info['to_name']);
                    foreach(explode(',',$email_info['to_email']) as $key=>$to_email)
                    {
                        if($email_info['account_login']!=$to_email)
                        {
                            $email = ((isset($to_name[$key]) and strlen($to_name[$key])) ? $to_name[$key] . ' <' . $to_email . '>' : $to_email);
                            $choices[$email] = $email;
                        }
                    }
                    
                    //print_rr($choices);
                    
                    echo select_tag('mail_to[]',$choices,$choices,['class'=>'form-control required','multiple'=>'multiple']);
                                        
                    ?>	    	  	  
                </p>
            </div>			
        </div>

        <?php
        $subject = TEXT_EXT_EMAIL_SUBJECT_RE . ' ' . $email_info['subject_cropped'];
        ?>	  

        <div class="form-group">
            <label class="col-md-2 control-label" for="is_active"><?php echo TEXT_EXT_EMAIL_SUBJECT ?></label>
            <div class="col-md-10">	
                <p class="form-control-static"><?php echo htmlspecialchars($subject) . input_hidden_tag('subject', $subject) ?></p>
            </div>			
        </div>

        <?php
        $body = '<br/><br/>' . format_date_time($email_info['date_added'], CFG_MAIL_DATETIME_FORMAT) . ', ' . $email_info['account_name'] . ' &lt;' . $email_info['account_login'] . '&gt;:<br/><blockquote>' . (strlen($email_info['body']) ? $email_info['body'] : nl2br($email_info['body_text'])) . '</blockquote>';

        if (strlen($signature = mail_accounts_users::get_signature()))
        {
            //$body .= '<br><br>' . $signature;
        }
        ?>
        <div class="form-group">	
            <label class="col-md-2 control-label" for="is_active">
                <?php echo TEXT_EXT_MAIL_BODY ?>
                <?= mail_templates::render_dropdown_helper() ?>
            </label>	 	
            <div class="col-md-10">	
                <?php echo textarea_tag('body', $body, array('class' => 'editor-auto-focus')) ?>
            </div>			
        </div>

        <div class="form-group">	
            <label class="col-md-2 control-label" for="is_active"><?php echo TEXT_ATTACHMENTS ?></label>	 	
            <div class="col-md-10">	
                <?php require(component_path('ext/mail/attachments_button')); ?>
            </div>			
        </div>



    </div>
</div> 

<?php echo ajax_modal_template_footer(TEXT_BUTTON_SEND) ?>

</form> 

<script>
    $(function () {

        $('#mail_form').validate({
            ignore: '',
            rules: {
                "body": {
                    required: function (element) {
                        CKEDITOR_holders["body"].updateElement();
                        return true;
                    }
                },
            },
            submitHandler: function (form) {
                app_prepare_modal_action_loading(form)
                form.submit();
            }
        });

    });
</script>


<?php require(component_path('ext/mail/mail_to.js')); ?>