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

//print_rr($_GET);

//check if requried GET params exist
if(isset($_GET['id']) and isset($_GET['file']) and isset($_GET['path']) and strlen(CFG_PUBLIC_ATTACHMENTS))
{
	$field_id = _get::int('id');
	$file = urldecode($_GET['file']);
	$path = explode('-',$app_path);
	$current_entity_id = (int)$path[0];
	$current_item_id = (int)$path[1];
	
	//check if field is publick and entity and field exit
	if(in_array($field_id,explode(',',CFG_PUBLIC_ATTACHMENTS)) and isset($app_entities_cache[$current_entity_id]) and isset($app_fields_cache[$current_entity_id][$field_id]))
	{
		//check item
		$item_query = db_query("select field_{$field_id} from app_entity_{$current_entity_id} where id={$current_item_id} and length(field_{$field_id})>0");
		if($item = db_fetch_array($item_query))
		{			
			//check field value
			if(in_array($file,explode(',',$item['field_' . $field_id])))
			{
				$file = attachments::parse_filename($file);
				
				//check file
				if(is_file($file['file_path']))
				{
					if($file['is_image'])
					{													
						$size = getimagesize($file['file_path']);										
						header("Content-type: " . $size['mime']);
						header('Content-Disposition: filename="' . $file['name'] . '"');
						
						flush();
						
						readfile($file['file_path']);
					}			
					else
					{										 
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename='.$file['name']);
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate');
						header('Pragma: public');
						header('Content-Length: ' . filesize($file['file_path']));
						
						flush();
									
						readfile($file['file_path']);					
					}
					
					exit;
				}	
			}
		}
	}
}	

header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
echo TEXT_FILE_NOT_FOUD;

exit();
