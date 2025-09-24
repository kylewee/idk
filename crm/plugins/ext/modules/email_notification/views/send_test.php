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

    $subject =  str_replace(['${current_date}','${current_date_time}'],[format_date(time()), format_date_time(time())],$rule_info['subject']);
?>

<?php echo ajax_modal_template_header($subject) ?>

<?php echo form_tag('email_form', url_for('ext/email_notification/send_test','action=send&entities_id=' . $rule_info['entities_id'] . '&id=' . $rule_info['id'] )) ?>

<div class="modal-body ajax-modal-width-1100"> 
    <div class="email-notification-preview">
    <?php
        $email_notification_rules = new email_notification_rules($rule_info);
        echo $email_notification_rules->get_body();
    ?>
    </div>    
</div>

<?php echo ajax_modal_template_footer(TEXT_SEND,input_tag('email',$app_user['email'],['class'=>'form-control input-medium','style'=>'display:inline-block', 'type'=>'email'])) ?>

</form>    

<script>

    $(function ()
    {
        $('#email_form').validate({ignore: '',
            submitHandler: function (form)
            {
                app_prepare_modal_action_loading(form)
                return true;
            }
        });        
    });
   
</script>
