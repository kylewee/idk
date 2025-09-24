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

<ul class="page-breadcrumb breadcrumb">
  <li><?php echo link_to(TEXT_EXT_EXPORT_TEMPLATES,url_for('ext/templates/export_templates'))?><i class="fa fa-angle-right"></i></li>
  <li><?php echo $template_info['entities_name'] ?><i class="fa fa-angle-right"></i></li>
  <li><?php echo link_to($template_info['name'],url_for('ext/templates_docx/blocks','templates_id=' . $template_info['id'])) ?><i class="fa fa-angle-right"></i></li>  
  <li><?php echo TEXT_TABLE . ' (' . TEXT_MYSQL_QUERY. ')' ?></li>  
</ul>
<?= strlen($parent_block['notes']) ? '<div class="alert alert-info">' . $parent_block['notes'] . '</div>':'' ?>

<p><?php echo TEXT_EXT_EXPORT_TEMPLATES_TABLE_BLOCK_TIP ?></p>

<?php
    $block_type = 'thead'; 
    require(component_path('ext/templates_docx/extra_rows')) 
?>

<?php
    $blocks_query = db_query("select b.* from app_ext_items_export_templates_blocks b where  block_type='body_cell' and b.templates_id = " . $template_info['id'] . " and b.parent_id = " . $parent_block['id'] . " order by b.sort_order, b.id",false);
    $blocks_count = db_num_rows($blocks_query);
?>

<h3 class="page-title"><?php echo TEXT_COLUMNS ?></h3>

<?php echo button_tag(TEXT_BUTTON_ADD,url_for('ext/templates_docx/blocks_mysql_table_form','templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id'])) ?>
<?php //if($blocks_count>1) echo ' ' . button_tag(TEXT_SORT_ORDER, url_for('ext/report_page/sort_blocks','block_type=body_cell&redirect_to=blocks_mysql_table&block_id=' . $block_info['id']), true, array('class' => 'btn btn-default')) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>
    <th><?php echo TEXT_ID ?></th>    
    <th><?php echo TEXT_TYPE ?></th>
    <th><?php echo TEXT_VALUE ?></th>
    <th width="100%"><?php echo TEXT_HEADING ?></th> 
    
    <th><?php echo TEXT_SORT_ORDER ?></th>                   
  </tr>
</thead>
<tbody>

<?php 

if($blocks_count==0) echo '<tr><td colspan="6">' . TEXT_NO_RECORDS_FOUND. '</td></tr>';

while($blocks = db_fetch_array($blocks_query))
{    
    $settings = new settings($blocks['settings']);
?>
<tr>  
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/templates_docx/blocks_mysql_table_delete','id=' . $blocks['id'] . '&templates_id=' . $template_info['id']. '&parent_block_id=' . $parent_block['id'])) . ' ' . button_icon_edit(url_for('ext/templates_docx/blocks_mysql_table_form','id=' . $blocks['id'] . '&templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id'])) ?></td>  
  <td><?php echo $blocks['id'] ?></td>  
  <td><?php 
 
        switch($settings->get('value_type'))
        {
            case 'text':                
                $value = TEXT_TEXT;
                break;
            case 'numeric':                
                $value = TEXT_NUMBER;
                break;
            case 'date':                
                $value = TEXT_DATE;
                break;
            case 'php_code':
                $value = TEXT_PHP_CODE;
                break;
            default:
                $value = TEXT_EMPTY_VALUE;
                break;
        }
        
        echo $value;
      ?>
  </td>
  <td><?= $settings->get('column') ?></td>
  <td><?php echo $settings->get('heading') ?></td>  
  <td><?php echo $blocks['sort_order'] ?></td>
</tr>  

<?php    
}
?>

</tbody>
</table>
</div>

<?php
    $block_type = 'tfoot'; 
    require(component_path('ext/templates_docx/extra_rows')) 
?>

<?php echo '<a href="' . url_for('ext/templates_docx/blocks','templates_id=' . $template_info['id']) . '" class="btn btn-default"><i class="fa fa-angle-left" aria-hidden="true"></i> ' . TEXT_BUTTON_BACK . '</a>';?>

<?php require(component_path('ext/templates_docx/table_preview')) ?>