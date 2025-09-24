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

<h3 class="page-title"><?php echo TEXT_EXT_INSTALLATION . ' ' . PLUGIN_EXT_VERSION ?></h3>

<?php 
	echo '
		<a href="' . url_for('ext/ext/install') . '" class="btn btn-primary" id="install_btn">' . TEXT_EXT_BUTTON_INSTALL . '</a>
		<div class="fa fa-spinner fa-spin hidden"></div>		
		';
?>

<script>
  $(function(){
		$('#install_btn').click(function(){
			$(this).addClass('hidden');
			$('.fa-spinner').removeClass('hidden')
		})
	})
</script>

