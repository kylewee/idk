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


$html = '
				<div class="table-scrollable">
			    <table class="table table-striped table-bordered table-hover" data-fixed-head="1">
			      <thead>
			        <tr>
								<th>' . TEXT_ENTITY . '</th>
								<th width="100%">' . TEXT_TITLE . '</th>
							</tr>
						</thead>
						<tbody>';

while ($items = db_fetch_array($items_query))
{
    if($items['entities_id']=='ipages')
    {
         $html .= '
                <tr>
                    <td></td>
                    <td><a href="' . url_for('ext/ipages/view', 'id=' .  $items['id']) . '">' . $items['title'] . '</a></td>
                </tr>
             ';
    }
    else
    {
        if (!strlen($items['title']) or !in_array($app_fields_cache[$items['entities_id']][fields::get_heading_id($items['entities_id'])]['type'],['fieldtype_input','fieldtype_input_masked','fieldtype_input_email']))
        {
            $title = items::get_heading_field($items['entities_id'], $items['id']);        
        }
        else
        {
            $title = $items['title'];
        }

        if ($app_entities_cache[$items['entities_id']]['parent_id'] > 0)
        {
            $path_info = items::get_path_info($items['entities_id'], $items['id']);
            $title = $path_info['parent_name'] . ' <i class="fa fa-angle-right"></i> ' . $title;
        }


        $html .= '
                                                    <tr>
                                                            <td>' . $app_entities_cache[$items['entities_id']]['name'] . '</td>
                                                            <td style="white-space:normal"><a href="' . url_for('items/info', 'path=' . $items['entities_id'] . '-' . $items['id']) . '">' . $title . '</a>
                                                                            <small>' . global_search::render_fields_in_listing(
                        $items['entities_id'],
                        $items['id'],
                        $entities_cfg_holder[$items['entities_id']]['fields_in_listing'],
                        '',
                        $entities_cfg_holder[$items['entities_id']]['cfg'],
                        $entities_cfg_holder[$items['entities_id']]['heading_field_id']) . '</small>
                                                            </td>
                                                    </tr>
                                                    ';
    }
}

if ($listing_split->number_of_rows == 0)
{
    $html .= '
			    <tr>
			      <td colspan="100">' . TEXT_NO_RECORDS_FOUND . '</td>
			    </tr>
			  ';
}

$html .= '
						</tbldy>
					</table>
				</div>
					';

//add pager
$html .= '
				<div class="row">
				  <div class="col-md-4 col-sm-12">' . $listing_split->display_count() . '</div>
				  <div class="col-md-8 col-sm-12">' . $listing_split->display_links() . '</div>
				</div>
				';
