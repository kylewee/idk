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

<?php echo ajax_modal_template_header($app_entities_cache[$entity_id]['name'] . ' (' . $call_history['phone']. ')'); ?>

<div class="modal-body">
<?php

$current_phone = preg_replace('/\D/','',$call_history['phone']);
$where_sql = [];
foreach($app_fields_cache[$entity_id] as $field)
{
    if($field['type']=='fieldtype_phone')
    {                
        $where_sql[] = "REGEXP_REPLACE(field_{$field['id']},'[^0-9]','')='{$current_phone}'";
    }
}

if(count($where_sql))
{
    $field_heading_id = fields::get_heading_id($entity_id);
    
    $html = '
        <div  style="max-height: 500px; overflow-y:auto">
        <table class="table table-striped table-bordered table-hover">';
    
    $item_query = db_query("select * " . fieldtype_formula::prepare_query_select($entity_id,'',false,['fields_in_query'=>$field_heading_id]) . " from app_entity_{$entity_id} where (" . implode(' or ', $where_sql).  ") order by id desc");
    while($item = db_fetch_array($item_query))
    {
        $html .= '
            <tr>
                <td>' . link_to(items::get_heading_field($entity_id, $item['id'], $item),url_for('items/info','path=' . $entity_id . '-' . $item['id']),['target'=>'_blank']). '</td>
                <td>' . format_date_time($item['date_added']). '</td>
            </tr>
            ';
    }
    
    $html .= '
        </table>
        </div>
        ';
    
    echo $html;
}
    
?>
</div>


<?php echo ajax_modal_template_footer('hide-save-button') ?>
