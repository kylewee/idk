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
<h3 class="page-title"><?php echo TEXT_PDF_EXPORT_FONTS ?></h3>
<p><?php echo TEXT_PDF_EXPORT_FONTS_INFO ?></p>

<?php
$rootDir = $fontDir = '';
$fonts_list = json_decode(file_get_contents(CFG_PATH_TO_DOMPDF_FONTS . '/installed-fonts.json'),true);

$fonts_list = is_array($fonts_list) ? $fonts_list : [];

//print_rr($fonts_list);

echo button_tag(TEXT_ADD, url_for('configuration/pdf_form'), true);
?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_NAME ?></th>        
    <th><?php echo TEXT_FILENAME ?></th>        
  </tr>
</thead>
<tbody>
    <?php
        foreach($fonts_list as $font_name=>$font_types)
        {
            echo '
                <tr>
                    <td>' . $font_name . '</td>
                    <td>'  . str_replace('//','/',$font_types['normal']) . '</td>
                </tr>
                ';
        }
    ?>    
</tbody>
</table>
</div>
<?php 
    echo TEXT_FONTS_FOLDER . ': ' . CFG_PATH_TO_DOMPDF_FONTS
?>
