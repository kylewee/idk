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

<ul class="page-breadcrumb breadcrumb">
    <li><?php echo link_to(TEXT_EXT_EXPORT_TEMPLATES, url_for('ext/templates/export_templates')) ?><i class="fa fa-angle-right"></i></li>
    <li><?php echo $template_info['entities_name'] ?><i class="fa fa-angle-right"></i></li>
    <li><?php echo $template_info['name'] ?><i class="fa fa-angle-right"></i></li>
    <li><?php echo TEXT_EXT_INFO_BLOCKS ?></li>
</ul>

<p><?php echo TEXT_EXT_EXPORT_TEMPLATES_XLSX_BLOCK_TIP ?></p>

<?php echo button_tag(TEXT_BUTTON_ADD, url_for('ext/templates_xlsx/blocks_form', 'templates_id=' . $template_info['id'])) ?>&nbsp;

<div class="table-scrollable">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th><?php echo TEXT_ACTION ?></th>
                <th><?php echo TEXT_INSERT ?></th>        
                <th><?php echo TEXT_ENTITY ?></th>
                <th width="100%"><?php echo TEXT_FIELD ?></th>
                <th><?php echo TEXT_SORT_ORDER ?></th>                    
            </tr>
        </thead>
        <tbody>

            <?php
            $blocks_query = db_query("select b.*, f.name, f.entities_id, f.type as field_type,f.configuration as field_configuration from app_ext_items_export_templates_blocks b, app_fields f, app_entities e where b.parent_id=0 and block_type='parent' and b.fields_id=f.id and b.templates_id = " . $template_info['id'] . " and f.entities_id=e.id order by b.sort_order, b.id");

            if(db_num_rows($blocks_query) == 0)
                echo '<tr><td colspan="6">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';

            while($blocks = db_fetch_array($blocks_query))
            {
                $block_settings = new settings($blocks['settings']);
                ?>
                <tr>
                    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/templates_xlsx/blocks_delete_confirm', 'id=' . $blocks['id'] . '&templates_id=' . $template_info['id'])) . ' ' . button_icon_edit(url_for('ext/templates_xlsx/blocks_form', 'id=' . $blocks['id'] . '&templates_id=' . $template_info['id'])) ?></td>
                    <td><?php
                        if($block_settings->get('display_us') == 'table')
                        {
                            echo '<input value="$table{block_' . $blocks['id'] . '}" readonly="readonly" class="form-control input-small select-all" style="width: 185px !important;">';
                        }
                        else
                        {
                            echo '<input value="${block_' . $blocks['id'] . '}" readonly="readonly" class="form-control input-small select-all">';
                        }
                        ?></td>
                    <td><?php echo $app_entities_cache[$blocks['entities_id']]['name'] ?></td>
                    <td><?php
                        $cfg = new fields_types_cfg($blocks['field_configuration']);

                        $field_name = fields_types::get_option($blocks['field_type'], 'name', $blocks['name']);

                        //check if subentity
                        if($blocks['field_type'] == 'fieldtype_id' and $app_entities_cache[$blocks['entities_id']]['parent_id'] == $template_info['entities_id'])
                        {
                            $blocks['field_type'] = 'fieldtype_entity';
                            $field_name = TEXT_LIST_RELATED_ITEMS;
                            
                            $cfg->cfg['entity_id'] = $blocks['entities_id'];
                        }

                        switch($blocks['field_type'])
                        {
                            case 'fieldtype_created_by':
                            case 'fieldtype_entity':
                            case 'fieldtype_entity_ajax':
                            case 'fieldtype_related_records':
                            case 'fieldtype_entity_multilevel':
                            case 'fieldtype_users':
                            case 'fieldtype_users_ajax':
                                if(in_array($cfg->get('display_as'), ['dropdown']) or $blocks['field_type'] == 'fieldtype_created_by')
                                {
                                    $field_name = '<a href="' . url_for('ext/templates_xlsx/entity_blocks', 'templates_id=' . $template_info['id'] . '&parent_block_id=' . $blocks['id']) . '"><i class="fa fa-list"></i> ' . $field_name . '</a>';
                                }
                                elseif($block_settings->get('display_us') == 'table')
                                {
                                    $field_name = '<a href="' . url_for('ext/templates_xlsx/table_blocks', 'templates_id=' . $template_info['id'] . '&parent_block_id=' . $blocks['id']) . '"><i class="fa fa-list"></i> ' . $field_name . '</a>';

                                    $field_name .= '
                                            <table>
                                                <tr>
                                                    <td>' . TEXT_EXT_ROWS_NUMBER . ':</td>
                                                    <td style="padding: 0 10px;"><input value="${count_' . $blocks['id'] . '}" readonly="readonly" class="form-control  input-sm  select-all" style="width: 150px;"></td>
                                                    <td><input value="${text_' . $blocks['id'] . '}" readonly="readonly" class="form-control input-sm select-all" style="width: 150px;"></td>
                                                </tr>
                                                <tr>
                                                    <td>' . TEXT_EXT_LINE_NUMBERING . ':</td>
                                                    <td style="padding: 0 10px;"><input value="${num_' . $blocks['id'] . '}" readonly="readonly" class="form-control  input-sm  select-all" style="width: 150px;"></td>                                
                                                </tr>
                                            </table>
                                        ';
                                }

                                if(in_array($block_settings->get('display_us'), ['table', 'inline']))
                                {
                                    $entity_id = (in_array($blocks['field_type'], ['fieldtype_created_by', 'fieldtype_users', 'fieldtype_users_ajax', 'fieldtype_users_approve', 'fieldtype_user_roles']) ? 1 : $cfg->get('entity_id'));

                                    $reports_id = default_filters::get_reports_id($entity_id, 'templates_xlsx_block' . $blocks['id'], false);
                                    $count_filters = reports::count_filters_by_reports_id($reports_id);

                                    $field_name .= '
                                        <div>
                                            ' . link_to(TEXT_FILTERS . ' (' . $count_filters . ')', url_for('default_filters/filters', 'reports_id=' . $reports_id . '&redirect_to=templates_xlsx_block' . $blocks['id'])) . '</span>'
                                            . ' | ' . link_to_modalbox(TEXT_SORT_ORDER, url_for('reports/sorting', 'reports_id=' . $reports_id . '&redirect_to=templates_xlsx_block' . $blocks['id'])) .
                                            '</div>';
                                }


                                break;
                        }


                        echo $field_name;

                        $tooltip = '';
                        if(strlen($block_settings->get('date_format')))
                        {
                            $tooltip = TEXT_DATE_FORMAT . ': ' . $block_settings->get('date_format');
                        }

                        if(strlen($block_settings->get('number_in_words')))
                        {
                            $tooltip = TEXT_EXT_NUMBER_IN_WORDS . ': ' . $block_settings->get('number_in_words');
                        }


                        if(strlen($tooltip))
                        {
                            echo tooltip_text($tooltip);
                        }
                        ?>
                    </td>
                    <td><?php echo $blocks['sort_order'] ?></td>
                </tr>  

                <?php
            }
            ?>

        </tbody>
    </table>
</div>

<?php echo '<a href="' . url_for('ext/templates/export_templates') . '" class="btn btn-default"><i class="fa fa-angle-left" aria-hidden="true"></i> ' . TEXT_BUTTON_BACK . '</a>'; ?>