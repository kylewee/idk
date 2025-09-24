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

<?php echo ajax_modal_template_header(TEXT_ADD) ?>

<?php echo form_tag('prepare_add_item_form', url_for('ext/pivot_calendars/view', 'id=' . $reports['id']), array('class' => 'form-horizontal')) ?>

<div class="modal-body">
    <div class="form-body ajax-modal-width-790">

        <?php
        $css = '';
        $reports_entities_query = db_query("select ce.*, e.name from app_ext_pivot_calendars_entities ce, app_entities e where e.id=ce.entities_id and ce.calendars_id='" . $reports['id'] . "' order by e.sort_order, e.name");
        while($reports_entities = db_fetch_array($reports_entities_query))
        {
            if(users::has_users_access_name_to_entity('create', $reports_entities['entities_id']))
            {
                $url_params = "&redirect_to=pivot_calendars" . $reports_entities['id'] . '&start=' . urlencode($_GET['start']) . '&end=' . urlencode($_GET['end']) . '&view_name=' . $_GET['view_name'];

                if($app_entities_cache[$reports_entities['entities_id']]['parent_id'] > 0)
                {
                    $reports_info = reports::create_default_entity_report($reports_entities['entities_id'], 'entity_menu');
                    $url = url_for('reports/prepare_add_item', 'reports_id=' . $reports_info['id'] . $url_params);
                }
                else
                {
                    $url = url_for("items/form", 'path=' . $reports_entities['entities_id'] . $url_params);
                }

                echo '<a href="javascript: open_dialog(\'' . $url . '\')" class="btn btn-primary btn-block pivot_calendars_entities' . $reports_entities['id'] . '">' . $reports_entities['name'] . '</a>';
                
                $css .= app_button_color_css($reports_entities['bg_color'],'pivot_calendars_entities' . $reports_entities['id']);
            }
        }
        
        echo $css;
        ?>  

    </div>
</div> 

<?php echo ajax_modal_template_footer('hide-save-button') ?>

</form>

