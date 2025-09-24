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

<?php echo ajax_modal_template_header(TEXT_CHANGE_SKIN) ?>

<div class="skins-list">
  <ul>
  <?php foreach(app_get_skins_choices(false) as $skin=>$name): ?>
    <li>
      <?php echo $name; ?>
      <div style="border: 1px solid #b9b9b9; margin: 5px; width: 80px; height: 80px; cursor: pointer; background: white;" onClick="location='<?php echo url_for('users/change_skin','action=change_skin&set_skin=' . $skin);?>'">
        <?php echo image_tag('css/skins/' . $skin . '/' . $skin . '.png'); ?>
      </div>
    </li>
  <?php endforeach ?>
  </ul>
</div>

<?php echo ajax_modal_template_footer('hide-save-button') ?>