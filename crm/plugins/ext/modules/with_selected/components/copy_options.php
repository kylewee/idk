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


$entity_cfg = entities::get_cfg($current_entity_id);

$html = '';

//copy comment
if($entity_cfg['use_comments']==1)
{
	$html .='
								<div class="form-group">
							  	<label class="col-md-3 control-label" for="settings_copy_comments">' . TEXT_EXT_COPY_COMMENTS . '</label>
							    <div class="col-md-9">
							  	  ' . select_tag('settings[copy_comments]',['0'=>TEXT_NO,'1'=>TEXT_YES],0,array('class'=>'form-control input-small')) . '
							    </div>
							  </div>
								';
}

//copy related items
$choices = [];
$fields_query = db_query("select f.id, f.name from app_fields f, app_forms_tabs t where f.type in ('fieldtype_related_records') and f.entities_id='" . db_input($current_entity_id) . "' and f.forms_tabs_id=t.id");
while($field = db_fetch_array($fields_query))
{
	$choices[$field['id']] = $field['name'];
}

if(count($choices))
{
	$html .='
							<div class="form-group">
						  	<label class="col-md-3 control-label" for="settings_copy_related_items">' . TEXT_EXT_COPY_RELATE_RECORDS . '</label>
						    <div class="col-md-9">
						  	  ' . select_tag('settings[copy_related_items][]',$choices,'',array('class'=>'form-control chosen-select','multiple'=>'multiple')) . '
						    </div>
						  </div>
							';
}

//coy sub entities
$choices = [];
$entities_query = db_query("select * from app_entities where parent_id='" . db_input($current_entity_id) . "' order by sort_order,name");
while($entities = db_fetch_array($entities_query))
{
	$choices[$entities['id']] = $entities['name'];
}

if(count($choices))
{
	$html .='
							<div class="form-group">
						  	<label class="col-md-3 control-label" for="settings_copy_sub_entities">' . TEXT_EXT_COPY_SUB_ENTITIES . '</label>
						    <div class="col-md-9">
						  	  ' . select_tag('settings[copy_sub_entities][]',$choices,'',array('class'=>'form-control chosen-select','multiple'=>'multiple')) . '
						    </div>
						  </div>
							';
}

echo $html;

