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

<?php echo form_tag('configuration_form', url_for('ext/pivot_map_reports/reports', 'action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '')), array('class' => 'form-horizontal')) ?>
<div class="modal-body">
    <div class="form-body">


        <div class="form-group">
            <label class="col-md-4 control-label" for="name"><?php echo TEXT_NAME ?></label>
            <div class="col-md-8">	
                <?php echo input_tag('name', $obj['name'], array('class' => 'form-control input-large required')) ?>        
            </div>			
        </div>

        <div class="form-group">
            <label class="col-md-4 control-label" for="in_menu"><?php echo TEXT_IN_MENU ?></label>
            <div class="col-md-8">	
                <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('in_menu', '1', array('checked' => $obj['in_menu'])) ?></label></div>
            </div>			
        </div> 


        <?php
        $choices = [];
        for($i = 3; $i <= 18; $i++)
        {
            $choices[$i] = $i;
        }
        ?>
        <div class="form-group">
            <label class="col-md-4 control-label" for="allowed_groups"><?php echo TEXT_DEFAULT_ZOOM ?></label>
            <div class="col-md-8">	
                <?php echo select_tag('zoom', $choices, $obj['zoom'], array('class' => 'form-control input-small')) ?>
            </div>			
        </div>

        <div class="form-group">
            <label class="col-md-4 control-label" for="name"><?php echo TEXT_DEFAULT_POSITION ?></label>
            <div class="col-md-8">	
                <?php echo input_tag('latlng', $obj['latlng'], array('class' => 'form-control input-medium')) ?>
                <?php echo tooltip_text(TEXT_DEFAULT_POSITION_TIP) ?>        
            </div>			
        </div>
        
        <div class="form-group">
            <label class="col-md-4 control-label" for="display_sidebar"><?php echo tooltip_icon(TEXT_EXT_MAP_SIDEBAR_TIP) . TEXT_EXT_DISPLAY_OBJECT_LIST ?></label>
            <div class="col-md-8">	
                <div class="checkbox-list"><?php echo select_tag('display_sidebar', ['0'=>TEXT_NO,'1'=>TEXT_YES],$obj['display_sidebar'], array('class' => 'form-control input-small')) ?></div>
            </div>			
        </div> 
        
        <div class="form-group" form_display_rules="display_sidebar:1">
            <label class="col-md-4 control-label" for="sidebar_width"><?php echo TEXT_SIDEBAR_WIDTH ?></label>
            <div class="col-md-8">
                <div class="input-group input-small">
                    <?php echo input_tag('sidebar_width', $obj['sidebar_width'], array('class' => 'form-control input-small')) ?>
                    <span class="input-group-addon">px</span>
                </div>
            </div>			
        </div>

        <div class="form-group">
            <label class="col-md-4 control-label" for="display_legend"><?php echo tooltip_icon(TEXT_EXT_ENTITIES_DISPLAY_LEGEND_TIP) . TEXT_EXT_DISPLAY_LEGEND ?></label>
            <div class="col-md-8">	
                <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('display_legend', '1', array('checked' => $obj['display_legend'])) ?></label></div>
            </div>			
        </div> 
        
        <p class="form-section"><?= TEXT_ACCESS ?></a>

        <div class="form-group">
            <label class="col-md-4 control-label" for="allowed_groups"><?php echo tooltip_icon(TEXT_EXT_USERS_GROUPS_INFO) . TEXT_EXT_USERS_GROUPS ?></label>
            <div class="col-md-8">	
                <?php echo select_tag('users_groups[]', access_groups::get_choices(false), $obj['users_groups'], array('class' => 'form-control input-xlarge chosen-select', 'multiple' => 'multiple')) ?>
            </div>			
        </div>
        
        <div class="form-group">
            <label class="col-md-4 control-label" for="is_public_access"><?php echo tooltip_icon(TEXT_EXT_PUBLIC_ACCESS_REPORT_INFO) . TEXT_EXT_PUBLIC_ACCESS ?></label>
            <div class="col-md-8">	
                <div class="checkbox-list"><label class="checkbox-inline"><?php echo input_checkbox_tag('is_public_access', '1', array('checked' => $obj['is_public_access'])) ?></label></div>
            </div>			
        </div> 

    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
    $(function ()
    {
        $('#configuration_form').validate({
            submitHandler: function (form)
            {
                app_prepare_modal_action_loading(form)
                form.submit();
            }
        });
    });

</script>   