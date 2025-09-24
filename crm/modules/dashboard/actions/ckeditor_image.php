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

//check if there are file to upload
if(isset($_FILES['upload']))
{
  if(strlen($_FILES['upload']['name'])>0)
  { 
    //check if this is image
    if(is_image($_FILES['upload']['tmp_name']))
    {      
      $file = attachments::prepare_image_filename($_FILES['upload']['name']);
      
      //check if file xeist
      $original_filename = pathinfo($file['file'], PATHINFO_FILENAME);
      $fileext = pathinfo($file['file'], PATHINFO_EXTENSION);
      $filename = $original_filename . '.' . $fileext; 
      $counter = 2;
      while(file_exists(DIR_WS_IMAGES  . $file['folder']  .'/'. $filename)) 
      {
          $filename = $original_filename . '(' . $counter . ').' . $fileext;
          $counter++;
      };
                          
      if(move_uploaded_file($_FILES['upload']['tmp_name'], DIR_WS_IMAGES  . $file['folder']  .'/'. $filename))
      {                                
        $response = array("uploaded" => 1,
                          "fileName" => $file['file'],
                          "url"=>DIR_WS_IMAGES  . $file['folder']  .'/'. $filename);
                          
        echo json_encode($response);                      
      }
      else
      {
        //return default error
        $response = array("uploaded" => 0,
                          "error" => array('message'=>sprintf(TEXT_ERROR_IMAGE_FILE_IS_NOT_UPLOADED,CFG_SERVER_UPLOAD_MAX_FILESIZE)),
                          );
                          
        echo app_json_encode($response);  
      }  
    }                       
  }
}

exit();