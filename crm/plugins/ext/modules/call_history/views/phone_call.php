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

<?php 
    $phone = preg_replace("/\D/","",$_GET['phone']);	 
?>

<?php echo ajax_modal_template_header(TEXT_EXT_CALL_TO_NUMBER . ' ' . $phone) ?>

<div class="modal-body">    
<?php 

$module_info_query = db_query("select * from app_ext_modules where id='" . _GET('module_id') . "' and type='telephony' and is_active=1");
if($module_info = db_fetch_array($module_info_query))
{
    modules::include_module($module_info,'telephony');
    
    $module = new $module_info['module'];
    $module->call_to_number($module_info['id'],$phone);    
}	

?>
</div>
 
<?php echo ajax_modal_template_footer('hide-save-button') ?>

 