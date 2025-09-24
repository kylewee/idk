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

<h3 class="page-title"><?php echo TEXT_HEADING_SERVER_INFORMATION ?></h3>    
      
<?php

if (function_exists('ob_start')) {

?>
<style type="text/css">
.server_info{font-size: 10px;}
.server_info .p {text-align: left;}
.server_info .e {background-color: #ccccff; font-weight: bold; border-bottom: 1px solid Gray;}
.server_info .h {background-color: #9999cc; font-weight: bold; border-bottom: 1px solid Gray;}
.server_info .v {background-color: #cccccc; border-bottom: 1px solid Gray;}
.server_info i {color: #666666;}
.server_info hr {display: none;}
.server_info h1 {font-size: 18px;}
.server_info h2 {font-size: 16px;}
</style>
<?php

  ob_start();
  phpinfo();
  $phpinfo = ob_get_contents();
  ob_end_clean();

  $phpinfo = str_replace('border: 1px', '', $phpinfo);
  preg_match('/<body>(.*)<\/body>/is', $phpinfo, $regs);
  echo '<div class="server_info">' . $regs[1] . '<div>';
}
  
?>
