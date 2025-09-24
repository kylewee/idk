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

<?php echo ajax_modal_template_header(TEXT_GENERAL_INFO) ?>

<?php 
$settings = new settings($obj['settings']);

echo form_tag('report_page_form', url_for('ext/report_page/reports', 'action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '')), array('enctype' => 'multipart/form-data', 'class' => 'form-horizontal')) ?>

<div class="modal-body">
    <div class="form-body">

        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_SETTINGS ?></a></li>
            <li><a href="#access_configuration"  data-toggle="tab"><?php echo TEXT_ACCESS ?></a></li>               
            <li><a href="#css_tab"  data-toggle="tab">CSS</a></li>               
            <li><a href="#filters_panels"  data-toggle="tab"><?php echo TEXT_FILTERS_PANELS ?></a></li>
        </ul>  

        <div class="tab-content">
            <div class="tab-pane fade active in" id="general_info">

                <div class="form-group">
                    <label class="col-md-3 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
                    <div class="col-md-9">	
                        <p class="form-control-static"><?php echo input_checkbox_tag('is_active', $obj['is_active'], array('checked' => ($obj['is_active'] == 1 ? 'checked' : ''))) ?></p>
                    </div>			
                </div>
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('name', $obj['name'], array('class' => 'form-control input-xlarge required')) ?>
                    </div>			
                </div>  

                <div class="form-group">
                    <label class="col-md-3 control-label" for="entities_id"><?php echo TEXT_ENTITY ?></label>
                    <div class="col-md-9">
                        <?php echo select_tag('entities_id', ['0'=>TEXT_NONE]+entities::get_choices(), $obj['entities_id'], array('class' => 'form-control chosen-select input-xlarge')) ?>
                        <?php echo tooltip_text(TEXT_EXT_REPORT_LINKED_TO_ENTITY_INFO) ?>
                    </div>			
                </div>
                
                <div class="form-group" form_display_rules="entities_id:0">
                    <label class="col-md-3 control-label" for="in_dashboard"><?php echo TEXT_IN_DASHBOARD ?></label>
                    <div class="col-md-9">	
                        <p class="form-control-static"><?php echo input_checkbox_tag('in_dashboard', $obj['in_dashboard'], array('checked' => ($obj['in_dashboard'] == 1 ? 'checked' : ''))) ?></p>
                    </div>			
                </div>
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="name"><?php echo TEXT_USE_HTML_EDITOR ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('use_editor',['1'=>TEXT_YES,'0'=>TEXT_NO], $obj['use_editor'], array('class' => 'form-control input-small')) ?>
                    </div>			
                </div>
                                
              
            <div form_display_rules="entities_id:!0">
                <?php
                $choices = [                                        
                    'print' => TEXT_EXT_PRINTABLE_REPORT,                    
                    'page' => TEXT_REPORT . ' (' . TEXT_EXT_SINGLE_PAGE . ')',
                ];
                ?>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="type"><?php echo TEXT_TYPE ?></label>
                    <div class="col-md-9">
                        <?php echo select_tag('type', $choices, $obj['type'], array('class' => 'form-control input-medium required')) ?>                        
                    </div>			
                </div> 
                
                <div form_display_rules="type:print">
                    <?php
                        $choices = [];
                        $choices['print'] = TEXT_PRINT;                    
                        $choices['pdf'] = 'PDF';                    
                    ?>

                    <div class="form-group">
                        <label class="col-md-3 control-label" ><?php echo  TEXT_SAVE_AS ?></label>
                        <div class="col-md-9">	
                            <?php echo select_tag('save_as[]',$choices, $obj['save_as'], array('class' => 'form-control input-large chosen-select','multiple'=>'multiple')) ?>	  	  
                        </div>			
                    </div>
                    
                    <div class="form-group form-group-page-orientation">
                        <label class="col-md-3 control-label" for="page_orientation"><?php echo TEXT_EXT_PAGE_ORIENTATION ?></label>
                        <div class="col-md-9">	
                            <?php echo select_tag('page_orientation', array('portrait' => TEXT_EXT_PAGE_ORIENTATION_PORTRAIT, 'landscape' => TEXT_EXT_PAGE_ORIENTATION_LANDSCAPE), $obj['page_orientation'], array('class' => 'form-control input-medium')) ?>  	        
                        </div>			
                    </div>


                    <div class="form-group">
                        <label class="col-md-3 control-label" for="name"><?php echo tooltip_icon(TEXT_ENTER_TEXT_PATTERN_INFO) . TEXT_FILENAME ?></label>
                        <div class="col-md-9">	
                            <?php echo input_tag('save_filename', $obj['save_filename'], array('class' => 'form-control input-large')) ?>	  	  
                        </div>			
                    </div>

                                        
                    <hr>                                
                 
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="button_title"><?php echo TEXT_EXT_PROCESS_BUTTON_TITLE; ?></label>
                        <div class="col-md-9">	
                            <?php echo input_tag('button_title', $obj['button_title'], array('class' => 'form-control input-large')); ?> 
                        </div>			
                    </div> 

                    <?php
                    $choices = array();
                    $choices['default'] = TEXT_EXT_IN_RECORD_PAGE;
                    $choices['menu_more_actions'] = TEXT_EXT_MENU_MORE_ACTIONS;                
                    $choices['menu_print'] = TEXT_EXT_PRINT_BUTTON;
                    ?>
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="button_position"><?php echo TEXT_EXT_PROCESS_BUTTON_POSITION; ?></label>
                        <div class="col-md-9">	
                            <?php echo select_tag('button_position[]', export_templates::get_position_choices(), $obj['button_position'], array('class' => 'form-control input-xlarge chosen-select required', 'multiple' => 'multiple')); ?> 
                        </div>			
                    </div> 

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="menu_icon"><?php echo TEXT_ICON; ?></label>
                        <div class="col-md-9">	
                            <?php echo input_icon_tag('button_icon', $obj['button_icon'], array('class' => 'form-control input-large')); ?>                             
                        </div>			
                    </div> 

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="button_color"><?php echo TEXT_EXT_PROCESS_BUTTON_COLOR ?></label>
                        <div class="col-md-9">
                            <div class="input-group input-small color colorpicker-default" data-color="<?php echo (strlen($obj['button_color']) > 0 ? $obj['button_color'] : '#428bca') ?>" >
                                <?php echo input_tag('button_color', $obj['button_color'], array('class' => 'form-control input-small')) ?>
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">&nbsp;</button>
                                </span>
                            </div>		  	  
                        </div>			
                    </div>
                                    
                
                    <hr>
                
                </div>    
                
            </div>
                
                
                
                <div id="menu_icon_settings">
                    <hr>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo TEXT_HIDE_PAGE_TITLE ?></label>
                        <div class="col-md-9">	
                            <?php echo select_tag_boolean('settings[hide_page_title]', $settings->get('hide_page_title')) ?>      
                        </div>			
                    </div> 

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo TEXT_HIDE_PRINT_BUTTON ?></label>
                        <div class="col-md-9">	
                            <?php echo select_tag_boolean('settings[hide_print_button]', $settings->get('hide_print_button')) ?>      
                        </div>			
                    </div> 
                    
                    <hr>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="menu_icon"><?php echo TEXT_MENU_ICON_TITLE; ?></label>
                        <div class="col-md-9">	
                            <?php echo input_icon_tag('icon', $obj['icon'], array('class' => 'form-control input-large')); ?>                         
                        </div>			
                    </div>  

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo TEXT_COLOR ?></label>
                        <div class="col-md-9">
                            <?php echo input_color('icon_color', $obj['icon_color']) ?>	    			  	    
                        </div> 
                    </div>    
                    
                </div>
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_SORT_ORDER ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('sort_order', $obj['sort_order'], array('class' => 'form-control input-xsmall')) ?>
                    </div>			
                </div> 

            </div>

            <div class="tab-pane fade" id="access_configuration">

                <div class="form-group">
                    <label class="col-md-3 control-label" for="users_groups"><?php echo TEXT_USERS_GROUPS ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('users_groups[]', access_groups::get_choices(), $obj['users_groups'], ['class' => 'form-control input-xlarge chosen-select', 'multiple' => 'multiple']) ?>      
                    </div>			
                </div> 

                <div class="form-group">
                    <label class="col-md-3 control-label" for="users_groups"><?php echo TEXT_ASSIGNED_TO ?></label>
                    <div class="col-md-9">	
                        <?php
                        $attributes = array('class' => 'form-control input-xlarge chosen-select',
                            'multiple' => 'multiple',
                            'data-placeholder' => TEXT_SELECT_SOME_VALUES);

                        $assigned_to = (strlen($obj['assigned_to']) > 0 ? explode(',', $obj['assigned_to']) : '');
                        echo select_tag('assigned_to[]', users::get_choices(), $assigned_to, $attributes);
                        ?>  	        
                    </div>			
                </div> 

            </div>
            
<?php
$toolitp = '';
if($obj['id']>0)
{
    $toolitp = TEXT_EXAMPLE . ':
        #report_page_' . $obj['id'] . '{
        &nbsp;&nbsp;&nbsp;&nbsp;font-size: 14px;
        }
        ';
}
?>
            
            <div class="tab-pane fade" id="css_tab">
                
                <div class="form-group">                    
                    <div class="col-md-12">	
                        <?php echo textarea_tag('css', $obj['css'],['class'=>'code_mirror','mode'=>'css']) ?>  
                        <?= (strlen($toolitp) ? tooltip_text($toolitp):'') ?>
                    </div>
                </div>
            </div>    
                    
            
            <div class="tab-pane fade" id="filters_panels">
                
                <div class="form-group">
                    <label class="col-md-3 control-label"><?php echo TEXT_FILTER_BY_DATES ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag_toogle('settings[filter_by_date]', $settings->get('filter_by_date')) ?>      
                    </div>			
                </div> 
                
<?php
    $choices = [
        'day'=>TEXT_EXT_DAY,
        'month'=>TEXT_EXT_MONTH,
        'year'=>TEXT_EXT_YEAR,
        'date_range'=>TEXT_DATE_RANGE,
        
    ];
?>                
                <div class="form-group" form_display_rules="settings_filter_by_date:1">
                    <label class="col-md-3 control-label"><?php echo TEXT_EXT_CALENDAR_USE_VIEW ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('settings[filter_by_date_mode]', $choices, $settings->get('filter_by_date_mode'),['class'=>'form-control input-medium']) ?>      
                    </div>			
                </div> 
                
                <div class="form-group" form_display_rules="settings_filter_by_date:1">
                    <label class="col-md-3 control-label"><?php echo TEXT_HEADING ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('settings[filter_by_date_heading]', $settings->get('filter_by_date_heading'),['class'=>'form-control input-large']) ?>      
                        <?= tooltip_text(TEXT_DEFAULT . ': ' . TEXT_DATE) ?>
                    </div>			
                </div> 
                
                <div class="form-section"></div>
                
                <div class="form-group">
                    <label class="col-md-3 control-label"><?php echo TEXT_FILTER_BY_USERS ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag_toogle('settings[filter_by_user]', $settings->get('filter_by_user')) ?>      
                    </div>			
                </div>
                
                <div class="form-group" form_display_rules="settings_filter_by_user:1">
                    <label class="col-md-3 control-label" for="users_groups"><?php echo TEXT_USERS_GROUPS ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('settings[filter_by_user_allowed_groups][]', access_groups::get_choices(), $settings->get('filter_by_user_allowed_groups'), ['class' => 'form-control input-xlarge chosen-select', 'multiple' => 'multiple']) ?>      
                    </div>			
                </div>
                <div class="form-group" form_display_rules="settings_filter_by_user:1">
                    <label class="col-md-3 control-label"><?php echo TEXT_HEADING ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('settings[filter_by_user_heading]', $settings->get('filter_by_user_heading'),['class'=>'form-control input-large']) ?>      
                        <?= tooltip_text(TEXT_DEFAULT . ': ' . TEXT_USER) ?>
                    </div>			
                </div> 
                
                <div class="form-section"></div>
                
               <?php
                    $filters = new report_page\report_filters($obj);
                    echo $filters->render_entities_filters_form(); 
               ?>
                
               <div class="form-section"></div>
                
               <?php
                    $filters = new report_page\report_filters($obj);
                    echo $filters->render_list_filters_form(); 
               ?>
                
            </div>
               

        </div>  

    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>

</form> 

<?php echo app_include_codemirror(['css']) ?>

<script>
    function menu_icon_settings_display()
    {
        if($('#entities_id').val()==0 || ($('#entities_id').val()>0 && $('#type').val()=='page'))
        {
           $('#menu_icon_settings').show();  
        }
        else
        {
           $('#menu_icon_settings').hide(); 
        }
    }
    
    $(function ()
    {
        $('#report_page_form').validate({
            submitHandler: function (form)
            {
                app_prepare_modal_action_loading(form)
                return true;
            }
        });
        
        menu_icon_settings_display()
        
        $('#entities_id').change(function(){
            menu_icon_settings_display()
        })
        
        $('#type').change(function(){
            menu_icon_settings_display()
        })

    });       

</script>  