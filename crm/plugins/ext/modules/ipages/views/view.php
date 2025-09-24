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

<?php $ipage = db_find('app_ext_ipages',$_GET['id']); ?>

<h3 class="page-title"><?php echo $ipage['name'] ?></h3>

<div class="ipage-description">
  <?php 
  	  	
  	echo ipages::prepare_attachments_in_text($ipage['description'], $ipage['attachments']);
  	  	  	
  	$output_options = array('class'=>'fieldtype_attachments',
  			'value'=>$ipage['attachments'],
  			'path'=>1,
  	        'is_ipages'=>_GET('id'),
  			'field'=>array('entities_id'=>1,'configuration'=>''),
  			'item'=>array('id'=>$ipage['id']));
  	
  	echo fields_types::output($output_options);
  	
  ?>     
</div>

<?php echo (strlen($ipage['html_code']??'') ? $ipage['html_code'] : '')?>