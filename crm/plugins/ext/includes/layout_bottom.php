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

<?php if(in_array($app_module_path,['ext/resource_timeline/view','ext/pivot_calendars/view','ext/calendar/personal','ext/calendar/public','ext/calendar/report','dashboard/dashboard','dashboard/reports','dashboard/reports_groups']) ): ?>
<script type="text/javascript" src="js/fullcalendar-scheduler/6.1.10/dist/index.global.min.js"></script>
<?php 
if(is_file($language_file_path = 'js/fullcalendar-scheduler/6.1.10/packages/core/locales/' . APP_LANGUAGE_SHORT_CODE . '.global.js')) 
  echo '<script type="text/javascript" src="' . $language_file_path . '"></script>';
?>
<?php endif ?>

<?php if(in_array($app_module_path,['ext/pivotreports/view','dashboard/dashboard','dashboard/reports','dashboard/reports_groups'])): ?>
<script type="text/javascript" src="js/PapaParse-master/papaparse.min.js"></script>
<script type="text/javascript" src="js/pivottable-master/dist/pivot.js"></script>
<script type="text/javascript" src="js/pivottable-master/dist/c3.min.js"></script>
<script type="text/javascript" src="js/pivottable-master/dist/d3.min.js"></script>
<script type="text/javascript" src="js/pivottable-master/dist/c3_renderers.js"></script>
<script type="text/javascript" src="js/pivottable-master/dist/export_renderers.js"></script>
<script type="text/javascript" src="<?php echo url_for('ext/pivotreports/view','id=' . (isset($_GET['id']) ? (int)$_GET['id']:0) . '&action=get_localization')?>"></script>
<?php endif ?>


<?php if($app_module=='timeline_reports' and $app_action=='view'): ?>
<script type="text/javascript" src="js/timeline-2.9.1/timeline.js"></script>
<?php endif ?>

<?php if(in_array($app_module_path,['report_page/view','ext/graphicreport/view','ext/funnelchart/view','dashboard/dashboard','dashboard/reports','dashboard/reports_groups','ext/pivot_tables/view']) ): ?>
<script src="js/highcharts/11.0.0/highcharts.js"></script>
<script src="js/highcharts/11.0.0/accessibility.js"></script>
<script type="text/javascript" src="js/highcharts/11.0.0/modules/funnel.js"></script>
<script type="text/javascript" src="js/highcharts/11.0.0/modules/exporting.js"></script>
<?php endif ?>

<script type="text/javascript" src="js/templates/templates.js"></script>
<script type="text/javascript" src="js/timer/timer.js?v=2"></script>

<!-- chat -->
<script type="text/javascript" src="js/ion.sound-master/js/ion.sound.js"></script>
<script type="text/javascript" src="js/ion.sound-master/js/init.js.php"></script>
<script type="text/javascript" src="js/app-chat/app-chat.js?v=1"></script>
<?php require(component_path('ext/app_chat/chat_button')) ?>


<!-- pivot table -->
<script src="js/webdatarocks/1.3.3/webdatarocks.toolbar.min.js"></script>
<script src="js/webdatarocks/1.3.3/webdatarocks.js"></script>
<script src="js/webdatarocks/1.3.3/webdatarocks.highcharts.js"></script>

<?php
//force print template
    echo export_templates::force_print_template();
?>



