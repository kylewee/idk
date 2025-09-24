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

<h3 class="page-title"><?php echo TEXT_SETTINGS ?></h3>

<p><?php echo TEXT_EXT_MAIL_INTEGRATION_INFO . '<br>' . DIR_FS_CATALOG . 'cron/email_fetch.php' ?></p>

<?php echo form_tag('configuration_form', url_for('ext/mail_integration/settings', 'action=save'), array('class' => 'form-horizontal')) ?>
<div class="form-body">


    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_MAIL_INTEGRATION"><?php echo TEXT_EXT_MAIL_INTEGRATION ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[MAIL_INTEGRATION]', ['1' => TEXT_YES, '0' => TEXT_NO], CFG_MAIL_INTEGRATION, array('class' => 'form-control input-small')) ?>        
        </div>			
    </div> 

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_MAIL_DISPLAY_IN_HEADER"><?php echo TEXT_DISPLAY_IN_HEADER ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[MAIL_DISPLAY_IN_HEADER]', ['1' => TEXT_YES, '0' => TEXT_NO], CFG_MAIL_DISPLAY_IN_HEADER, array('class' => 'form-control input-small')) ?>        
        </div>			
    </div> 

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_MAIL_DISPLAY_IN_MENU"><?php echo TEXT_DISPLAY_IN_MENU ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[MAIL_DISPLAY_IN_MENU]', ['1' => TEXT_YES, '0' => TEXT_NO], CFG_MAIL_DISPLAY_IN_MENU, array('class' => 'form-control input-small')) ?>        
        </div>			
    </div>
    
    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_MAIL_DISPLAY_IN_MENU"><?php echo TEXT_EXT_SHOW_UNREAD_EMAILS_TOP ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[SHOW_UNREAD_EMAILS_TOP]', ['1' => TEXT_YES, '0' => TEXT_NO], CFG_SHOW_UNREAD_EMAILS_TOP, array('class' => 'form-control input-small')) ?>        
        </div>			
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_MAIL_ROWS_PER_PAGE"><?php echo TEXT_ROWS_PER_PAGE ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('CFG[MAIL_ROWS_PER_PAGE]', CFG_MAIL_ROWS_PER_PAGE, array('class' => 'form-control input-small required number')); ?>
        </div>			
    </div> 

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_MAIL_DATETIME_FORMAT"><?php echo TEXT_DATETIME_FORMAT ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('CFG[MAIL_DATETIME_FORMAT]', CFG_MAIL_DATETIME_FORMAT, array('class' => 'form-control input-small required')) ?>
            <?php echo '<span class="help-block">' . TEXT_DATE_FORMAT_IFNO . '</span>'; ?>
        </div>			
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_MAIL_ALLOW_AUDIO_RECORDING"><?php echo tooltip_icon(TEXT_ALLOW_AUDIO_RECORDING_INFO) . TEXT_ALLOW_AUDIO_RECORDING; ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[MAIL_ALLOW_AUDIO_RECORDING]', ['1' => TEXT_YES, '0' => TEXT_NO], CFG_MAIL_ALLOW_AUDIO_RECORDING, array('class' => 'form-control input-small')); ?>       
        </div>			
    </div>

    <div class="form-group" form_display_rules="CFG_MAIL_ALLOW_AUDIO_RECORDING:1">
        <label class="col-md-3 control-label" for="CFG_MAIL_AUDIO_RECORDING_LENGTH"><?php echo tooltip_icon(TEXT_AUDIO_RECORDING_LENGTH_INFO) . TEXT_AUDIO_RECORDING_LENGTH; ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[MAIL_AUDIO_RECORDING_LENGTH]', [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9], CFG_MAIL_AUDIO_RECORDING_LENGTH, array('class' => 'form-control input-small')); ?>       
        </div>			
    </div>

</div>


<?php echo submit_tag(TEXT_BUTTON_SAVE) ?>
</form> 