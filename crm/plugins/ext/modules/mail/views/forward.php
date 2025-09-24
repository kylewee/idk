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

<?php echo form_tag('mail_form', url_for('ext/mail/forward','action=send&mail_id=' . $email_info['id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body ajax-modal-width-790">
  
  	<div class="form-group">
		 	<label class="col-md-2 control-label" for="is_active"><?php echo TEXT_EXT_EMAIL_FROM ?></label>
	    <div class="col-md-10">	
	  	  <?php echo select_tag('accounts_id',mail_accounts::get_choices_by_user('full') ,$email_info['accounts_id'],array('class'=>'form-control required')) ?>
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
		 	<label class="col-md-2 control-label" for="mail_to"><?php echo TEXT_EXT_EMAIL_TO ?></label>
	    <div class="col-md-10">	
	  	  <?php echo select_tag('mail_to[]',[],'',['class'=>'form-control required','multiple'=>'multiple']) ?>
	    </div>			
	  </div>
	  
<?php 
	$subject = TEXT_EXT_EMAIL_SUBJECT_FWD . ' ' . $email_info['subject_cropped'];	
?>	  
	  
	  <div class="form-group">
		 	<label class="col-md-2 control-label" for="is_active"><?php echo TEXT_EXT_EMAIL_SUBJECT ?></label>
	    <div class="col-md-10">	
	  	  <p class="form-control-static"><?php echo htmlspecialchars($subject) . input_hidden_tag('subject',$subject)?></p>
	    </div>			
	  </div>
	  
<?php 
  $forward_text = '<br/><br/>' . 
  '<b>' . TEXT_EXT_FORWARDED_MESSAGE . '</b><br/>' . 
  TEXT_EXT_EMAIL_SUBJECT . ': ' . $email_info['subject'] . '<br>' . 
  TEXT_DATE_ADDED . ': ' . format_date_time($email_info['date_added'], CFG_MAIL_DATETIME_FORMAT) . '<br>' . 
  TEXT_EXT_EMAIL_FROM . ': ' .  $email_info['from_name'] . ' &lt;' . $email_info['from_email'] . '&gt;<br/>' .  
  TEXT_EXT_EMAIL_TO . ': ' .  mail_info::render_mail_to_full($email_info) . '<br/>';
    		 			
	$body = $forward_text . '<br/><blockquote>' . (strlen($email_info['body']) ? $email_info['body'] : nl2br($email_info['body_text'])) . '</blockquote>';
	
	if(strlen($signature = mail_accounts_users::get_signature()))
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
	  	  <?php echo textarea_tag('body',$body,array('class'=>'editor')) ?>
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
