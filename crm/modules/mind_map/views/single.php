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
<div id="mind_map_options" data-url="<?php echo url_for('mind_map/single','entities_id=' . $current_entity_id. '&items_id=' .  $current_item_id. '&fields_id=' . $fields_id . '&action=save') ?>" data-is-editable="<?php echo $mind_map->is_editable() ?>"></div>

<script>	
	var mind_map_json = '<?php echo $mind_map->get_json() ?>';
</script>
