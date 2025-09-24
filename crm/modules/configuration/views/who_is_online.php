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

<h3 class="page-title"><?php echo TEXT_WHO_IS_ONLINE ?></h3>

<?php echo form_tag('cfg_form', url_for('configuration/save', 'redirect_to=configuration/who_is_online'), array('enctype' => 'multipart/form-data', 'class' => 'form-horizontal')) ?>
<div class="form-body">


    <div class="tabbable tabbable-custom">

        <ul class="nav nav-tabs">            
            <li class="active"><a href="#users_configuration"  data-toggle="tab"><?php echo TEXT_SETTINGS ?></a></li>   
        </ul>

        <div class="tab-content">            
            <div class="tab-pane fade active in" id="users_configuration">

                <div class="form-group">
                    <label class="col-md-3 control-label" for="CFG_WHO_IS_ONLINE_STATUS"><?php echo TEXT_ENABLE_WHO_IS_ONLINE_REPORT ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('CFG[WHO_IS_ONLINE_STATUS]', $default_selector, CFG_WHO_IS_ONLINE_STATUS, array('class' => 'form-control input-small required')); ?>
                        <?php echo tooltip_text(TEXT_ENABLE_WHO_IS_ONLINE_REPORT_TIP) ?>
                    </div>			
                </div> 

                <div class="form-group">
                    <label class="col-md-3 control-label" for="CFG_WHO_IS_ONLINE_INTERVAL"><?php echo TEXT_STATUS_UPDATE_TIME ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('CFG[WHO_IS_ONLINE_INTERVAL]', CFG_WHO_IS_ONLINE_INTERVAL, array('class' => 'form-control input-small required')); ?>
                        <?= tooltip_text(TEXT_IN_MINUTES) ?>
                    </div>			
                </div>  

            </div>

        </div>
    </div>

    <?php echo submit_tag(TEXT_BUTTON_SAVE) ?>

</div>
</form>

