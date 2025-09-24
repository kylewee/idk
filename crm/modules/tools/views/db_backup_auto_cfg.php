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

<?php echo ajax_modal_template_header(TEXT_SETTINGS) ?>

<?php echo form_tag('backup', url_for('configuration/save','redirect_to=tools/db_backup_auto'),array('class'=>'form-horizontal')) ?>
<div class="modal-body ajax-modal-width-790">    
<?php echo TEXT_AUTOMATIC_BACKUP_INFO ?>
    
    <div class="form-group">
        <label class="col-md-3 control-label" ><?php echo TEXT_CRON_BACKUP ?></label>
        <div class="col-md-9">	            
            <?php echo input_tag('cron_backup', DIR_FS_CATALOG . 'cron/backup.php', array('class' => 'form-control ','readonly'=>'readonly')); ?>                                        
        </div>			
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label" ><?php echo TEXT_BACKUP_FOLDER ?></label>
        <div class="col-md-9">	            
            <?php echo input_tag('cron_backup_folder', DIR_FS_BACKUPS_AUTO, array('class' => 'form-control ','readonly'=>'readonly')); ?>                                        
        </div>			
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label"><?php echo TEXT_KEEP_FIELS ?></label>
        <div class="col-md-9">	            
            <?php echo input_tag('CFG[AUTOBACKUP_KEEP_FILES_DAYS]',  CFG_AUTOBACKUP_KEEP_FILES_DAYS, array('class' => 'form-control input-small','type'=>'number')); ?>                            
            <?php echo tooltip_text(TEXT_ENTER_NUMBER_OF_DAYS) ?>
        </div>			
    </div>
</div> 
<?php echo ajax_modal_template_footer() ?>

</form>  

