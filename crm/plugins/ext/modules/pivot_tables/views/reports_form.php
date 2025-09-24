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

<?php echo ajax_modal_template_header(TEXT_INFO) ?>

<?php echo form_tag('reports_form', url_for('ext/pivot_tables/reports', 'action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '')), array('class' => 'form-horizontal')) ?>
<div class="modal-body">
    <div class="form-body">


        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_GENERAL_INFO ?></a></li>
            <li><a href="#graphic_report"  data-toggle="tab"><?php echo TEXT_EXT_GRAPHIC_REPORT ?></a></li>
            <li><a href="#access"  data-toggle="tab"><?php echo TEXT_ACCESS ?></a></li>    
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade active in" id="general_info">

                <div class="form-group">
                    <label class="col-md-4 control-label" for="name"><?php echo TEXT_NAME ?></label>
                    <div class="col-md-8">	
                        <?php echo input_tag('name', $obj['name'], array('class' => 'form-control input-xlarge required')) ?>        
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="type"><?php echo TEXT_REPORT_ENTITY ?></label>
                    <div class="col-md-8">	
                        <?php echo select_tag('entities_id', entities::get_choices(), $obj['entities_id'], array('class' => 'form-control input-xlarge required')) ?>        
                    </div>			
                </div>

                <?php
                $choices = [
                    '' => '',
                    'default' => TEXT_DEFAULT,
                    'quick_filters' => TEXT_QUICK_FILTERS_PANELS,
                ];
                ?>
                <div class="form-group">
                    <label class="col-md-4 control-label" for="default_view"><?php echo TEXT_FILTERS_PANELS ?></label>
                    <div class="col-md-8">	
                        <?php echo select_tag('filters_panel', $choices, $obj['filters_panel'], array('class' => 'form-control input-medium')) ?>        
                    </div>			
                </div>  


                <div class="form-group">
                    <label class="col-md-4 control-label" for="height"><?php echo tooltip_icon(TEXT_DEFAULT . ': 600') . TEXT_HEIGHT ?></label>
                    <div class="col-md-8">	
                        <?php echo input_tag('height', $obj['height'], array('class' => 'form-control input-small', 'type' => 'number')) ?>        
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="in_menu"><?php echo tooltip_icon(TEXT_EXT_DISPLYA_IN_MAIN_MENU_TIP) . TEXT_EXT_DISPLYA_IN_MAIN_MENU ?></label>
                    <div class="col-md-8">	
                        <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('in_menu', '1', array('checked' => $obj['in_menu'])) ?></label></div>
                    </div>			
                </div> 


                <div class="form-group">
                    <label class="col-md-4 control-label" for="name"><?php echo TEXT_SORT_ORDER ?></label>
                    <div class="col-md-8">	
                        <?php echo input_tag('sort_order', $obj['sort_order'], array('class' => 'form-control input-small')) ?>        
                    </div>			
                </div>

            </div>

            <div class="tab-pane fade" id="graphic_report">

                <?php
                $choices_chart_type = [
                    '' => '',
                    'pie' => TEXT_EXT_PIE_CHART,
                    'column' => TEXT_EXT_COLUMN_CHART,
                    'stacked_column' => TEXT_EXT_STACKED_COLUMN_CHART,
                    'stacked_percent' => TEXT_EXT_STACKED_PERCENT_COLUMN_CHART,
                    'bar' => TEXT_EXT_BAR_CHART,
                    'line' => TEXT_EXT_LINE_CHART,
                    'spline'=>TEXT_EXT_SPLINE_CHART,
                    'funnel' => TEXT_EXT_FUNNEL_CHART,
                    'pyramid' => TEXT_EXT_PYRAMID_CHART,
                    'area' => TEXT_EXT_AREA_CHART,
                    'stacked_area' => TEXT_EXT_STACKED_AREA_CHART,
                ];
                ?>
                <div class="form-group">
                    <label class="col-md-4 control-label" for="type"><?php echo TEXT_TYPE ?></label>
                    <div class="col-md-8">	
                        <?php echo select_tag('chart_type', $choices_chart_type, $obj['chart_type'], array('class' => 'form-control input-large')) ?>        
                    </div>			
                </div>

                <?php
                $choices = [
                    'right' => TEXT_ON_RIGHT,
                    'left' => TEXT_ON_LEFT,
                    'top' => TEXT_ON_TOP,
                    'bottom' => TEXT_ON_BOTTOM,
                    'only_chart' => TEXT_EXT_ONLY_CHART,
                        ]
                ?>
                <div class="form-group">
                    <label class="col-md-4 control-label" for="position"><?php echo TEXT_POSITION ?></label>
                    <div class="col-md-8">	
                        <?php echo select_tag('chart_position', $choices, $obj['chart_position'], array('class' => 'form-control input-medium')) ?>        
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="chart_height"><?php echo tooltip_icon(TEXT_DEFAULT . ': 600') . TEXT_HEIGHT ?></label>
                    <div class="col-md-8">	
                        <?php echo input_tag('chart_height', $obj['chart_height'], array('class' => 'form-control input-small', 'type' => 'number')) ?>        
                    </div>			
                </div>
                <?php
                $colors = strlen($obj['colors']) ? explode(',', $obj['colors']) : '';
                $types = strlen($obj['chart_types']) ? explode(',', $obj['chart_types']) : '';
                $html = '';
                for($i = 0; $i < 5; $i++)
                {
                    $html .=  '
                        <table style="margin-bottom: 5px;">
                            <tr>
                                <td>' . input_color('colors[' . $i . ']', (isset($colors[$i]) ? $colors[$i] : '')) . '</td>
                                <td style="padding-left: 10px;">' . select_tag('chart_types[' . $i . ']',$choices_chart_type, (isset($types[$i]) ? $types[$i]:''),array('class'=>'form-control input-large')). '</td>
                            </tr>
                        </table>';
                }
                $html .= '';
                ?>
                <div class="form-group">
                    <label class="col-md-4 control-label"><?php echo tooltip_icon(TEXT_EXT_PIVOT_TABLES_COLORS_TIP) . TEXT_COLOR ?></label>
                    <div class="col-md-8">	
                        <?php echo $html ?>        
                    </div>			
                </div>

                <p class="form-section"><?= TEXT_VALUE ?></p>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="cfg_numer_format"><?php echo tooltip_icon(TEXT_EXT_PIVOTREPORTS_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT ?></label>
                    <div class="col-md-8">	
                        <?php echo input_tag('chart_number_format', $obj['chart_number_format'], array('class' => 'form-control input-small input-masked', 'data-mask' => '9/~/~')) ?>	      
                    </div>			
                </div>
                
                <div class="form-group">
                    <label class="col-md-4 control-label" for="cfg_numer_format"><?php echo TEXT_PREFIX ?></label>
                    <div class="col-md-8">	
                        <?php echo input_tag('chart_number_prefix', $obj['chart_number_prefix'], array('class' => 'form-control input-small ')) ?>	      
                    </div>			
                </div>
                
                <div class="form-group">
                    <label class="col-md-4 control-label" for="cfg_numer_format"><?php echo TEXT_SUFFIX ?></label>
                    <div class="col-md-8">	
                        <?php echo input_tag('chart_number_suffix', $obj['chart_number_suffix'], array('class' => 'form-control input-small ')) ?>	      
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="chart_show_labels"><?php echo TEXT_EXT_SHOW_TOTALS_IN_CHART ?></label>
                    <div class="col-md-8 form-control-static">	
                        <?php echo input_checkbox_tag('chart_show_labels', 1, ['checked' => $obj['chart_show_labels']]) ?>        
                    </div>			
                </div>

            </div>

            <div class="tab-pane fade" id="access">    
                <?php
                $users_groups = strlen($obj['users_groups']) ? json_decode($obj['users_groups'], true) : array();
                foreach(access_groups::get_choices(false) as $group_id => $group_name)
                {
                    ?>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="allowed_groups"><?php echo $group_name ?></label>
                        <div class="col-md-8">	
                            <?php echo select_tag('access[' . $group_id . ']', array('' => '', 'view' => TEXT_VIEW_ONLY_ACCESS, 'full' => TEXT_FULL_ACCESS), (isset($users_groups[$group_id]) ? $users_groups[$group_id] : ''), array('class' => 'form-control input-medium')) ?>
                        </div>			
                    </div>
                    <?php
                }
                ?>


            </div>  
        </div>



    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
    $(function ()
    {
        $('#reports_form').validate({ignore: '',
            submitHandler: function (form)
            {
                app_prepare_modal_action_loading(form)
                return true;
            }
        });
        
        $(".input-masked").each(function(){
            $.mask.definitions["~"]="[,. ]";
            $(this).mask($(this).attr("data-mask"));
        }) 

    });
</script>  
