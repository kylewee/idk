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

<?php $obj = db_find('app_ext_mail_accounts',$_GET['id']); ?>

<?php echo form_tag('login', url_for('ext/mail_integration/accounts','action=delete&id=' . $_GET['id'] )) ?>
    
<div class="modal-body"> 
<?php
	$check_query = db_query("select id from app_ext_mail where accounts_id='" . _get::int('id'). "'");
	if($check = db_fetch_array($check_query))
	{
		echo TEXT_EXT_MAIL_ACCOUNT_DELETE_WARNING;
		$button_title = 'hide-save-button';		
	}
	else 
	{
		echo sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,$obj['name']);
		$button_title = TEXT_BUTTON_DELETE;
	}

?>   

</div> 
 
<?php echo ajax_modal_template_footer($button_title) ?>

</form>    
    