<?php

/* 
 *  Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 *  https://www.rukovoditel.net.ru/
 *  
 *  CRM Руководитель - это свободное программное обеспечение, 
 *  распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *  
 *  Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 *  Государственная регистрация программы для ЭВМ: 2023664624
 *  https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */


if($reports['filters_panel']=='quick_filters')
{
    $panel_fiters_reports_id = reports::auto_create_report_by_type($reports['entities_id'], 'panel_kanban_reports' . $reports['id'], true);
    $filters_panels = new filters_panels($reports['entities_id'],$panel_fiters_reports_id,'',0);
    $filters_panels->set_type('kanban_reports' . $reports['id']);
    $filters_panels->set_items_listing_funciton_name('load_kanban_report' . $reports['id']);
    echo '<div class="kanban_reports' . $reports['id'] . '">' . $filters_panels->render_horizontal() . '</div>';
}

$parent_entity_id = $app_entities_cache[$reports['entities_id']]['parent_id'];

$is_top_kanban = ($parent_entity_id>0 and $app_path==$reports['entities_id']) ? true:false;

//listing highlight rules

echo $listing_highlight->render_css();



$field = db_find('app_fields', $reports['group_by_field']);

$cfg = new fields_types_cfg($field['configuration']);

//use global lists if exsit
if ($cfg->get('use_global_list') > 0)
{
    $kanban_choices = global_lists::get_choices($cfg->get('use_global_list'), false);
}
else
{
    $kanban_choices = fields_choices::get_choices($field['id'], false);
}

//print_r($funnel_choices);

foreach ($kanban_choices as $id => $value)
{
    $kanban_info_choices[$id]['count'] = 0;

    if (strlen($reports['sum_by_field']))
    {
        foreach (explode(',', $reports['sum_by_field']) as $k)
        {
            $kanban_info_choices[$id][$k] = 0;
        }
    }
}

$kanban_width = ($reports['width'] > 0 ? $reports['width'] : 300);



$count_exclude_choices = (strlen($reports['exclude_choices']) ? count(explode(',', $reports['exclude_choices'])) : 0);

$html = '
  	<div class="kanban-div">	
  		<table class="kanban-table" style="width: ' . ($kanban_width * (count($kanban_choices) - $count_exclude_choices)) . 'px">
  			<tr>
  		';

foreach ($kanban_choices as $choices_id => $choices_name)
{           
    //exclude choices
    if (in_array($choices_id, explode(',', $reports['exclude_choices'])))
        continue;  
    
    //add icon to name
    $icon = $cfg->get('use_global_list') > 0 ? $app_global_choices_cache[$choices_id]['icon'] : $app_choices_cache[$choices_id]['icon'];
    if(strlen($icon))
    {
        $choices_name = app_render_icon($icon) . ' ' . $choices_name;
    }
    
    $items_query_sql = kanban::get_items_query($reports['group_by_field'] . ':' . $choices_id, $reports, $fiters_reports_id);
    $items_query = db_query($items_query_sql);
    while($items = db_fetch_array($items_query))
    {
        $kanban_info_choices[$choices_id]['count']++;

        //prepare sum by field
        if(strlen($reports['sum_by_field']))
        {
            foreach(explode(',', $reports['sum_by_field']) as $k)
            {
                if(strlen($items['field_' . $k]??''))
                    $kanban_info_choices[$choices_id][$k] += $items['field_' . $k];
            }
        }

    }

    $items_html = kanban::get_items_html([
        'choices_id' => $choices_id,
        'reports' => $reports,  
        'fiters_reports_id' => $fiters_reports_id,
        'listing_highlight' => $listing_highlight,
        'is_kanban_sotrtable' => $is_kanban_sotrtable,
    ]);
    
    $items_html = '<div id="kanban' . $reports['id'] . '_' . $choices_id . '_items">' . $items_html . '</div>';

    //prepare sum title  	
    $sum_html = '';
    if (strlen($reports['sum_by_field']))
    {
        $sum_html = '<table class="kanban-heading-sum">';
        foreach (explode(',', $reports['sum_by_field']) as $id)
        {
            $sum_html .= '
  					<tr>
  						<td>' . $app_fields_cache[$reports['entities_id']][$id]['name'] . ':&nbsp;</td>
  						<th>' . fieldtype_input_numeric::number_format($kanban_info_choices[$choices_id][$id], $app_fields_cache[$reports['entities_id']][$id]['configuration']) . '</th>
  					</tr>';
        }
        $sum_html .= '</table>';
    }


    $color = '';
    if ($cfg->get('use_global_list') > 0)
    {
        if (strlen($app_global_choices_cache[$choices_id]['bg_color']))
        {
            $color = 'style="border-color: ' . $app_global_choices_cache[$choices_id]['bg_color'] . '"';
        }
    }
    elseif (strlen($app_choices_cache[$choices_id]['bg_color']))
    {
        $color = 'style="border-color: ' . $app_choices_cache[$choices_id]['bg_color'] . '"';
    }

    $add_button = '';
    if (users::has_access('create', $access_schema) and $app_fields_cache[$reports['entities_id']][$reports['group_by_field']]['type'] != 'fieldtype_autostatus')
    {
        if($is_top_kanban)
        {
            $add_button = '<a class="btn btn-default btn-xs purple kanban-add-button" href="#" onClick="open_dialog(\'' . url_for('reports/prepare_add_item', 'reports_id=' . $fiters_reports_id . '&redirect_to=kanban-top' . $reports['id'] . '&fields[' . $reports['group_by_field'] . ']=' . $choices_id) . '\')"><i class="fa fa-plus" aria-hidden="true"></i></a>'; 
        }
        else
        {
            $add_button = '<a class="btn btn-default btn-xs purple kanban-add-button" href="#" onClick="open_dialog(\'' . url_for('items/form', 'path=' . $app_path . '&redirect_to=kanban' . $reports['id'] . '&fields[' . $reports['group_by_field'] . ']=' . $choices_id) . '\')"><i class="fa fa-plus" aria-hidden="true"></i></a>';
        }
    }

    $heading_html = '
  			<div id="kanban_heading_' . $choices_id . '" class="kanban-heading-block">
  				<div class="kanban-heading" ' . $color . '>
  					<div id="kanban_heading_content_' . $choices_id . '">			
	  					<div class="heading">' . $add_button . $choices_name . ' (' . $kanban_info_choices[$choices_id]['count'] . ')</div>
	  					<div>' . $sum_html . '</div>
	  				</div>
  				</div>
  			</div>
  			';


    $html .= '
  			<td class="kanban-table-td" style="width: ' . $kanban_width . 'px">
  			' . $heading_html . '
  			' . $items_html . '
  			</td>	
  			';
}

$html .= '
  			</tr>
  		</table>
  	</div>
  		';

echo $html;

?>

<script>
    
    appHandleUniform();
    
    function load_kanban_report<?= $reports['id'] ?>_sortable()
    {
        let report_id = '#kanban_board<?= $reports['id'] ?>'   
                
        //hander scrol action
        $(window).bind('scroll', function ()
        {    
            //fix heading block	        
            let offset_top = $(report_id +' .kanban-table').offset().top - $('.header').height();


            if ($(window).width() < 973)
            {
                offset_top = offset_top + 50;
            }
       
            let scrollTop = $(this).scrollTop();

            if (scrollTop > offset_top)
            {                
                $(report_id + ' .kanban-heading-block').css('transform', 'translateY(' + (scrollTop - offset_top) + 'px)');
                $(report_id + ' .kanban-heading-block').addClass('kanban-heading-block-transform')
            }
            else
            {
                $(report_id + ' .kanban-heading-block').css('transform', 'none');
                $(report_id + ' .kanban-heading-block').removeClass('kanban-heading-block-transform')
            }
        });
     
         
        
        <?= (!$is_kanban_sotrtable ? 'return false':'') ?>
                                
        $(report_id + " ul.kanban-sortable" ).sortable({
            connectWith: report_id + " ul.kanban-sortable",
            cancel: ".li-pagination-kanban",
                over: function (e, ui) {
                    $(report_id + " .kanban-sortable").removeClass("ul-kanban-hover")
                    target_id = $(e.target).attr("id").replace("kanban_choice_","");
                    $(report_id + " #kanban_choice_"+target_id).addClass("ul-kanban-hover")                                                      	  					                            
                },                
                create: function( event, ui ) {
                   prepare_kanban<?= $reports['id'] ?>_padding();     
                },
                stop: function( event, ui ) {
                    $(report_id + " .kanban-sortable").removeClass("ul-kanban-hover")
                    prepare_kanban<?= $reports['id'] ?>_padding();   
                },                           
                update: function(event,ui){  

                    var choices_id = this.id.replace("kanban_choice_","")

                    $(report_id + " #kanban_heading_"+choices_id).addClass("kanban-heading-loading");	  					    

                    if(ui.sender)
                    {
                        //alert(this.id+" - "+ui.item.attr("id"))  							
                        item_id = ui.item.attr("id").replace("kanban_item_","")
                        $.ajax({type: "POST",url:"<?= url_for("ext/kanban/view", "action=sort&id=" . $reports['id'] . "&path=" . $app_path)?>",data: {choices_id:choices_id,item_id:item_id}}).done(function(data){
                                if(data.length>0)
                        {
                                //alert(data)
                                obj = JSON.parse(data)
                                for (var k in obj) {
                                                  //console.log("obj." + k + " = " + obj[k]);

                                        $(report_id + " #kanban_heading_"+k).removeClass("kanban-heading-loading");
                                        $(report_id + " #kanban_heading_content_"+k).html(obj[k])
                                                }
                                }

                                });
                         }
                        else  
                        {
                            $(report_id + " #kanban_heading_"+choices_id).removeClass("kanban-heading-loading");
                            $(report_id + " .kanban-sortable").removeClass("ul-kanban-hover")
                        }
              }
           });
    }; 

    function prepare_kanban<?= $reports['id'] ?>_padding()
    {
        let report_id = '#kanban_board<?= $reports['id'] ?>'
        
        //get max height
        max_hight = 0;
        $(report_id + ' .kanban-sortable').each(function ()
        {
            max_hight = ($(this).height() > max_hight ? $(this).height() : max_hight)
        })

        //console.log(max_hight);

        //set padding
        $(report_id + ' .kanban-sortable').each(function ()
        {
            if ($(this).height() < max_hight)
            {
                padding = max_hight - $(this).height();
                $(this).css("padding-bottom", padding + 'px')
            }
        })
    }

    $(function ()
    {
        load_kanban_report<?= $reports['id'] ?>_sortable()                        
    });

</script>
