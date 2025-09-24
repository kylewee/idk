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

//print_r($_GET);
//print_r($_POST);

$entities_id = _get::int('path');
$items_id = _get::int('items_id');
$fields_id = _get::int('fields_id');


switch($app_module_action)
{
	case 'user_rolese_hold_change':
		$user_roles_dropdown_change_holder[$fields_id][_post::int('user_id')] = _post::int('role_id'); 
		break;				
}

if(isset($_POST['users']))
{
	$users = (is_array($_POST['users']) ? $_POST['users'] : (strlen($_POST['users']) ? [$_POST['users']] : []) );
}
else
{
	$users = [];
}

$roles_choices = user_roles::get_choices($fields_id,false);

if(!count($users) or !count($roles_choices)) exit();


if(count($roles_choices)==1)
{
	$html = '';
	foreach($users as $user_id)
	{
		$html .= input_hidden_tag('user_roles[' . $fields_id . '][' . $user_id . ']',key($roles_choices)); 
	}
}
else
{	
	$html = '
			<table class="table user-roles-table">	
			<tr>
				<th>' . TEXT_USER . '</th>
				<th>' . TEXT_ROLE. '</th>
			</tr>
			';
	
	foreach($users as $user_id)
	{
		$value = '';
		if($items_id>0)
		{
			$value = user_roles::get_role_to_items($fields_id, $entities_id, $items_id, $user_id);
		}
		
		if(isset($user_roles_dropdown_change_holder[$fields_id][$user_id]))
		{
			$value = $user_roles_dropdown_change_holder[$fields_id][$user_id];
		}
		
		$html .= '
				<tr>
					<td>' . $app_users_cache[$user_id]['name'] . '</td>
					<td>' . select_tag('user_roles[' . $fields_id . '][' . $user_id . ']',[''=>'']+$roles_choices,$value,['class'=>'form-control input-medium required','onChange'=>'user_rolese_' . $fields_id . '_hold_change(' . $user_id . ',this.value)']). '</td>
				</tr>
				';
	}
	
	$html .= '
			</table>';
}


echo $html;

exit();

