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

<h3 class="page-title"><?php echo TEXT_EXT_EXTRA_ROWS ?></h3>

<?php echo button_tag(TEXT_BUTTON_ADD,url_for('ext/report_page/extra_rows','action=add_row&block_type=' . $block_type . '&report_id=' . $report_page['id'] . '&block_id=' . $block_info['id']),false) ?>


<table class="table table-striped table-bordered table-hover margin-top-10">
<tbody>

<?php 

$blocks_query = db_query("select b.* from app_ext_report_page_blocks b where  b.block_type='" . $block_type . "' and  b.report_id = " . $report_page['id'] . " and b.parent_id = " . $block_info['id'] . " order by b.sort_order, b.id",false);

if(db_num_rows($blocks_query)==0) echo '<tr><td colspan="6">' . TEXT_NO_RECORDS_FOUND. '</td></tr>';

while($blocks = db_fetch_array($blocks_query))
{        
?>
<tr>
  <td style="white-space: nowrap;"><?php echo '<a onClick="return confirm(\'' . TEXT_ARE_YOU_SURE .'\')" href="' . url_for('ext/report_page/extra_rows','action=delete_row&id=' . $blocks['id'] . '&report_id=' . $report_page['id']. '&block_id=' . $block_info['id']) . '" class="btn btn-default btn-xs purple"><i class="fa fa-trash-o"></i></a>'  ?></td>    
  <td width="100%"><?php echo link_to(TEXT_EXT_ROW . ' (' . abs($blocks['sort_order']) . ')',url_for('ext/report_page/extra_rows','row_id=' . $blocks['id'] . '&report_id=' . $report_page['id'] . '&block_id=' . $block_info['id']))?></td>    
</tr>  

<?php    
}
?>

</tbody>
</table>
