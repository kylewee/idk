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
<h3 class="page-title"><?php echo TEXT_EMAIL_SMTP_CONFIGURATION ?></h3>

<?php echo form_tag('cfg', url_for('configuration/save'), array('class' => 'form-horizontal')) ?>
<?php echo input_hidden_tag('redirect_to', 'configuration/emails_smtp') ?>
<div class="form-body">

<div class="form-group">
    <label class="col-md-3 control-label" for="CFG_EMAIL_USE_SMTP"><?php echo TEXT_EMAIL_USE_SMTP ?></label>
    <div class="col-md-9">
        <?php echo select_tag('CFG[EMAIL_USE_SMTP]', $default_selector, CFG_EMAIL_USE_SMTP, array('class' => 'form-control input-small')); ?> 
    </div>			
</div>

<div class="form-group">
    <label class="col-md-3 control-label" for="CFG_EMAIL_SMTP_SERVER"><?php echo TEXT_EMAIL_SMTP_SERVER ?></label>
    <div class="col-md-9">	
        <?php echo input_tag('CFG[EMAIL_SMTP_SERVER]', CFG_EMAIL_SMTP_SERVER, array('class' => 'form-control input-large')); ?>
    </div>			
</div>  

<div class="form-group">
    <label class="col-md-3 control-label" for="CFG_EMAIL_SMTP_PORT"><?php echo TEXT_EMAIL_SMTP_PORT ?></label>
    <div class="col-md-9">	
        <?php echo input_tag('CFG[EMAIL_SMTP_PORT]', CFG_EMAIL_SMTP_PORT, array('class' => 'form-control input-small')); ?>
    </div>			
</div>

<?php
$choices = [
    '' => TEXT_NO,
    'ssl' => 'SSL (port 465)',
    'tls' => 'TLS (port 587)',
];
?>

<div class="form-group">
    <label class="col-md-3 control-label" for="CFG_EMAIL_SMTP_ENCRYPTION"><?php echo TEXT_EMAIL_SMTP_ENCRYPTION ?></label>
    <div class="col-md-9">	
        <?php echo select_tag('CFG[EMAIL_SMTP_ENCRYPTION]', $choices, CFG_EMAIL_SMTP_ENCRYPTION, array('class' => 'form-control input-medium')); ?>
    </div>			
</div>

<div class="form-group">
    <label class="col-md-3 control-label" for="CFG_EMAIL_SMTP_LOGIN"><?php echo TEXT_EMAIL_SMTP_LOGIN ?></label>
    <div class="col-md-9">	
        <?php echo input_tag('CFG[EMAIL_SMTP_LOGIN]', CFG_EMAIL_SMTP_LOGIN, array('class' => 'form-control input-large')); ?>
    </div>			
</div>

<div class="form-group">
    <label class="col-md-3 control-label" for="CFG_EMAIL_SMTP_PASSWORD"><?php echo TEXT_EMAIL_SMTP_PASSWORD ?></label>
    <div class="col-md-9">	
        <?php echo input_tag('CFG[EMAIL_SMTP_PASSWORD]', CFG_EMAIL_SMTP_PASSWORD, array('class' => 'form-control input-large')); ?>
    </div>			
</div> 

<div class="form-group">
    <label class="col-md-3 control-label" for="CFG_EMAIL_SMTP_ENCRYPTION"><?php echo TEXT_DEBUG_MODE ?></label>
    <div class="col-md-9">	
        <?php echo select_tag('CFG[EMAIL_SMTP_DEBUG]', $default_selector, CFG_EMAIL_SMTP_DEBUG, array('class' => 'form-control input-medium')) . tooltip_text('log/smtp_log.txt'); ?>
    </div>			
</div>  
    
    <?php echo submit_tag(TEXT_BUTTON_SAVE) ?>
    
    </div>
</form>

