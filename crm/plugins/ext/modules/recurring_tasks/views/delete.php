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

<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>


<?php echo form_tag('login', url_for('ext/recurring_tasks/' . (strlen($app_redirect_to) ? $app_redirect_to:'repeat'),'action=delete&id=' . $_GET['id']  . (strlen($app_path) ? '&path=' . $app_path:''))) ?>
    
<div class="modal-body">    
<?php echo sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,$_GET['id'])?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>    
    