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

  if(isset($_GET['path']))
  { 
    $path_info = items::parse_path($_GET['path']);
    $current_path = $_GET['path']; 
    $current_entity_id = $path_info['entity_id'];
    $current_item_id = true; // set to true to set off default title     
    $current_path_array = $path_info['path_array'];
    $app_breadcrumb = items::get_breadcrumb($current_path_array);
    
    require(component_path('items/navigation'));
  } 
   
?>

<h3 class="page-title"><?php echo $reports['name'] . icalendar::get_url($reports['enable_ical'],'report',$reports['id']) ?></h3>

<?php 
  
if($reports['filters_panel']=='default')
{
  $filters_preivew = new filters_preivew($fiters_reports_id);  
  $filters_preivew->redirect_to = 'calendarreport' . $_GET['id'];
  $filters_preivew->has_listing_configuration = false;
  
  if(isset($_GET['path']))
  {
    $filters_preivew->path = $_GET['path'];
    $filters_preivew->include_paretn_filters = false;
  }
  
  echo $filters_preivew->render();  
}
elseif($reports['filters_panel']=='quick_filters')
{
    $type = 'calendar_report_' . $reports['id'];
    $filters_panels = new filters_panels($reports['entities_id'],$fiters_reports_id,'',0);
    $filters_panels->set_type($type);
    $filters_panels->set_items_listing_funciton_name('refetch_calendar_events');
    echo '
        <div class="' . $type . '">' . $filters_panels->render_horizontal() . '</div>
        <script>
            function refetch_calendar_events()
            {
                calendarreport' . $reports['id'] . '.refetchEvents()               
            }
        </script>
        ';
}
    
  require(component_path('ext/calendar/report'));