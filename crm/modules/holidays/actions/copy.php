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

switch($app_module_action)
{
    case 'copy_selected':
        $selected_items = $_POST['selected_items']??'';
        $year = $_POST['year'];
        
        foreach(explode(',',$selected_items) as $id)
        {
            $holiday_query = db_query("select * from app_holidays where id={$id}");
            if($holiday = db_fetch_array($holiday_query))
            {
                $sql_data = array(
                    'name' => $holiday['name'],
                    'start_date' => $year . substr($holiday['start_date'],4),
                    'end_date' => $year . substr($holiday['end_date'],4),
                );
                
                db_perform('app_holidays', $sql_data);
            }
        }
        
        $holidays_filter = $year;
        
        redirect_to('holidays/holidays');
        break;
}

