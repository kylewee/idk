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

<h3 class="page-title"><?php echo TEXT_EXT_GLOBAL_SEARCH ?></h3>

<p><?php echo TEXT_EXT_GLOBAL_SEARCH_INFO ?></p>

<?php echo form_tag('configuration_form', url_for('ext/global_search/settings', 'action=save'), array('class' => 'form-horizontal')) ?>
<div class="form-body">

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_USE_GLOBAL_SEARCH"><?php echo TEXT_EXT_USE_GLOBAL_SEARCH ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[USE_GLOBAL_SEARCH]', app_get_boolean_choices(), CFG_USE_GLOBAL_SEARCH, array('class' => 'form-control input-small')) ?>        
        </div>			
    </div>


    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_GLOBAL_SEARCH_DISPLAY_IN_HEADER"><?php echo TEXT_DISPLAY_IN_HEADER ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[GLOBAL_SEARCH_DISPLAY_IN_HEADER]', app_get_boolean_choices(), CFG_GLOBAL_SEARCH_DISPLAY_IN_HEADER, array('class' => 'form-control input-small')) ?>        
        </div>			
    </div> 

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_GLOBAL_SEARCH_DISPLAY_IN_MENU"><?php echo TEXT_DISPLAY_IN_MENU ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[GLOBAL_SEARCH_DISPLAY_IN_MENU]', app_get_boolean_choices(), CFG_GLOBAL_SEARCH_DISPLAY_IN_MENU, array('class' => 'form-control input-small')) ?>        
        </div>			
    </div>   

    <div class="form-group">
        <label class="col-md-3 control-label" for="allowed_groups"><?php echo TEXT_EXT_USERS_GROUPS ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[GLOBAL_SEARCH_ALLOWED_GROUPS][]', access_groups::get_choices(), CFG_GLOBAL_SEARCH_ALLOWED_GROUPS, ['class' => 'form-control input-xlarge chosen-select', 'multiple' => 'multiple']) ?>
            <?php echo tooltip_text(TEXT_EXT_GLOBAL_SEARCH_ACCESS_TIP) ?>
        </div>			
    </div> 
    
    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_GLOBAL_SEARCH_IN_INFO_PAGES"><?php echo TEXT_EXT_SEARCH_IN_INFO_PAGES ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[GLOBAL_SEARCH_IN_INFO_PAGES]', app_get_boolean_choices(), CFG_GLOBAL_SEARCH_IN_INFO_PAGES, array('class' => 'form-control input-small')) ?>        
        </div>			
    </div> 

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_GLOBAL_SEARCH_ROWS_PER_PAGE"><?php echo TEXT_ROWS_PER_PAGE ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('CFG[GLOBAL_SEARCH_ROWS_PER_PAGE]', CFG_GLOBAL_SEARCH_ROWS_PER_PAGE, array('class' => 'form-control input-small number')); ?>
        </div>			
    </div>

    <h3 class="form-section"><?php echo TEXT_FIELDTYPE_INPUT_TITLE ?></h3>

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_GLOBAL_SEARCH_INPUT_TOOLTIP"><?php echo TEXT_TOOLTIP ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('CFG[GLOBAL_SEARCH_INPUT_TOOLTIP]', (defined('CFG_GLOBAL_SEARCH_INPUT_TOOLTIP') ? CFG_GLOBAL_SEARCH_INPUT_TOOLTIP : TEXT_SEARCH), array('class' => 'form-control input-medium')); ?>
        </div>			
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_GLOBAL_SEARCH_INPUT_MIN"><?php echo TEXT_MIN_VALUE ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('CFG[GLOBAL_SEARCH_INPUT_MIN]', CFG_GLOBAL_SEARCH_INPUT_MIN, array('class' => 'form-control input-small number')); ?>
        </div>			
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_GLOBAL_SEARCH_INPUT_MAX"><?php echo TEXT_MAX_VALUE ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('CFG[GLOBAL_SEARCH_INPUT_MAX]', CFG_GLOBAL_SEARCH_INPUT_MAX, array('class' => 'form-control input-small number')); ?>
        </div>			
    </div>       

</div>  

<?php echo submit_tag(TEXT_BUTTON_SAVE) ?>
</form>     