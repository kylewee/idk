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

<?php echo ajax_modal_template_header(TEXT_EXT_NEW_EMAIL) ?>

<?php 

$is_ajax = (isset($_GET['mail_to']) or strlen($app_redirect_to)) ? true:false;

echo form_tag('mail_form', url_for('ext/mail/create', 'action=send' . (strlen($app_path) ? '&path=' . $app_path : '')), array('class' => 'form-horizontal','is_ajax'=>$is_ajax, 'path'=>$app_path)) ?>

<?php echo input_hidden_tag('redirect_to', $app_redirect_to) ?>

<div class="modal-body">
    <div class="form-body ajax-modal-width-790">

        <div class="form-group">
            <label class="col-md-2 control-label" for="is_active"><?php echo TEXT_EXT_EMAIL_FROM ?></label>
            <div class="col-md-10">	
                <?php echo select_tag('accounts_id', mail_accounts::get_choices_by_user('full', true), (isset($app_mail_filters['accounts_id']) and $app_mail_filters['accounts_id'] > 0 ? $app_mail_filters['accounts_id'] : mail_accounts::get_default()), array('class' => 'form-control required')) ?>
            </div>			
        </div>
        
<?php
    $choices = mail_accounts::get_signature_choices();
    foreach($choices as $id=>$text)
    {
        echo input_hidden_tag('signature[' . $id . ']',$text);
    }
    
?>

<?php
$choices = [];

$mail_to = $_GET['mail_to']??'';
if(strlen($mail_to))
{    
    foreach(explode(',',$mail_to) as $email)
    {
        $email = trim($email);
        if(app_validate_email($email))
        {
            $choices[$email] = $email;
        }
    }
}
?>
        <div class="form-group">
            <label class="col-md-2 control-label" for="mail_to"><?php echo TEXT_EXT_EMAIL_TO ?></label>
            <div class="col-md-10">	
                <?php echo select_tag('mail_to[]',$choices, $mail_to, ['class' => 'form-control required', 'multiple' => 'multiple']) ?>
                <label id="mail_to-error" class="error" for="mail_to"></label>
            </div>			
        </div>

        <div class="form-group">
            <label class="col-md-2 control-label" for="subject"><?php echo TEXT_EXT_EMAIL_SUBJECT ?></label>
            <div class="col-md-10">	
                <?php echo input_tag('subject', '', ['class' => 'form-control required']) ?>
            </div>			
        </div>
	  
        <div class="form-group">	
            <label class="col-md-2 control-label" for="is_active">
                <?php echo TEXT_EXT_MAIL_BODY ?>
                <?= mail_templates::render_dropdown_helper() ?>
            </label>	 	
            <div class="col-md-10">	
                <?php echo textarea_tag('body', '', array('class' => 'editor required')) ?>
                <label id="body-error" class="error" for="body"></label>
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

<?php require(component_path('ext/mail/mail_to.js')); ?>