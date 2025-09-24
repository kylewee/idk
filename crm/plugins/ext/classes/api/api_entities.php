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

class api_entities extends api
{
    function get_entities()
    {
        $data = $this->get_tree();
        
        self::response_success($data);
    }
    
    function get_tree($parent_id = 0, $tree = array(), $level = 0, $path = array())
    {               
        $entities_query = db_query("select e.*, eg.name as group_name from app_entities e left join app_entities_groups eg on e.group_id=eg.id  where e.parent_id='" . $parent_id . "' order by eg.sort_order, eg.name, e.sort_order, e.name");
        
        while ($entities = db_fetch_array($entities_query))
        {        
            $tree[] = array(
                'id' => $entities['id'],
                'parent_id' => $entities['parent_id'],
                'group_id' => $entities['group_id'],
                'group_name' => $entities['group_name'],
                'name' => $entities['name'],
                'notes' => $entities['notes'],
                'sort_order' => $entities['sort_order'],
                'level' => $level,
                'path' => $path,
            );

            $tree = $this->get_tree($entities['id'], $tree, $level + 1, array_merge($path, array($entities['id'])));
        }

        return $tree;
    }
    
    function get_export_template()
    {
        global $app_user;
        
        $template_id = self::_post('template_id');
        $entity_id = self::_post('entity_id');
        $item_id = self::_post('item_id');
                        
        $template_info_query = db_query("select * from app_ext_export_templates where id=" . $template_id);
        if(!$template_info = db_fetch_array($template_info_query))
        {            
            self::response_error("Template #{$template_id} not found!",404);
        }
        
        $item_query = db_query("select e.*  " .  fieldtype_formula::prepare_query_select($entity_id, '') . " from app_entity_" . $entity_id . " e where e.id='" . $item_id . "'");
        if(!$item = db_fetch_array($item_query))
        {
            self::response_error("Item #{$item_id} not found in Entity #{$entity_id}!",404);
        }
        
        if($template_info['type']=='docx')
        {                
            require_once(CFG_PATH_TO_PHPWORD);

            $docx = new export_templates_blocks($template_info);
            $filename = $docx->prepare_template_file($entity_id, $item_id);
            
            readfile(DIR_FS_TMP . $filename);        
            unlink(DIR_FS_TMP . $filename);
            exit();
            
        }
        elseif($template_info['type']=='xlsx')
        {
            require(CFG_PATH_TO_PHPSPREADSHEET);
            require('includes/libs/PHPStep/0.2/vendor/autoload.php');

            $_POST['filename'] = '';
            $xlsx = new export_templates_xlsx($template_info);
            $xlsx->prepare_template_file($entity_id, $item_id);    
            echo $xlsx->download();

            exit();
        }
        else
        {            
            $template_info['template_header'] = export_templates::get_template_extra([$entity_id], $template_info, 'template_header');
            $template_info['template_footer'] = export_templates::get_template_extra([$entity_id], $template_info, 'template_footer');
            
            $export_template = $template_info['template_header'] . export_templates::get_html($entity_id, $item_id,$template_id) . $template_info['template_footer'];
      
            $html = '
            <html>
              <head>
                  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

                  <style>               
                    body { 
                        color: #000;
                        font-family: \'Open Sans\', sans-serif;
                        padding: 0px !important;
                        margin: 0px !important;                                   
                     }

                     body, table, td {
                      font-size: 12px;
                      font-style: normal;
                     }

                     table{
                       border-collapse: collapse;
                       border-spacing: 0px;                
                     }

                     ' . $template_info['template_css'] . '	

                  </style>

                   ' . ($template_info['page_orientation']=='landscape' ? '<style type="text/css" media="print"> @page { size: landscape; } </style>':''). '      						
              </head>        
              <body>
               ' . $export_template . '
               <script>
                  window.print();
               </script>            
              </body>
            </html>
            ';
            
            
            echo $html;
            exit();
        }
    }
}
