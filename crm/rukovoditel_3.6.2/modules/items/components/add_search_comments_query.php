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

if(app_parse_search_string($_POST['search_keywords'], $search_keywords))
{
  if (isset($search_keywords) && (sizeof($search_keywords) > 0)) 
  {
    $listing_sql_query .= " and (";
    for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) 
    {
      switch ($search_keywords[$i]) 
      {
        case '(':
        case ')':
        case 'and':
        case 'or':
          $listing_sql_query .= " " . $search_keywords[$i] . " ";
          break;
        default:
          $keyword = $search_keywords[$i];
          $listing_sql_query .= "description like '%" . db_input($keyword) . "%'";
          break;
      }
    }
    $listing_sql_query .= ")";
    
    
    if(count($search_keywords)==1 and is_numeric($search_keywords[0]) and $entity_cfg->get('display_comments_id')==1)
    {
      $listing_sql_query .= " or id='" . db_input($search_keywords[0]) . "'";
    }
    
    //echo $listing_sql_query;                
  } 
}
else
{
  echo '<div class="alert alert-danger">' . TEXT_ERROR_INVALID_KEYWORDS . '</div>';
} 
