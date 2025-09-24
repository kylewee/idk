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

<h3 class="page-title"><?php echo TEXT_EXT_API_ACCESS ?></h3>

<?php echo TEXT_EXT_API_ACCESS_INFO ?>

<?php echo form_tag('cfg', url_for('ext/ext/api', 'action=save'), array('class' => 'form-horizontal')) ?>

<div class="form-body">

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_USE_API"><?php echo TEXT_EXT_ALLOW_API ?></label>
        <div class="col-md-9">	
            <?php echo select_tag('CFG[USE_API]', $default_selector, CFG_USE_API, array('class' => 'form-control input-small')); ?>  	  
        </div>			
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_USE_API"><?php echo TEXT_URL ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('url', url_for_file('api/rest.php'), array('class' => 'form-control input-xlarge select-all', 'readonly' => 'readonly')); ?>  	  
        </div>			
    </div>
  
    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_API_KEY"><?php echo TEXT_EXT_API_KEY ?></label>
        <div class="col-md-9">	
            <?php echo textarea_tag('CFG[API_KEY]', CFG_API_KEY, array('class' => 'form-control input-xlarge textarea-small select-all')); ?>
            <?php echo tooltip_text('<a href="javascript:generate_api_key(40)">' . TEXT_EXT_GENERATE . '</a>') ?>
        </div>			
    </div> 
    
    <div class="form-group">
        <label class="col-md-3 control-label" for="CFG_USE_API"><?php echo TEXT_RESTRICTED_BY_IP ?></label>
        <div class="col-md-9">	
            <?php echo textarea_tag('CFG[API_ALLOWED_IP]',CFG_API_ALLOWED_IP, array('class' => 'form-control input-xlarge')); ?>  
            <?= tooltip_text(TEXT_EXT_ENTER_ALLOWED_IP_BY_COMMA) ?>
        </div>			
    </div>


    <?php echo submit_tag(TEXT_BUTTON_SAVE) ?>

</div>
</form>

<script>
    function generate_api_key(length)
    {
        if (confirm('<?php echo TEXT_ARE_YOU_SURE ?>'))
        {
            $('#CFG_API_KEY').val(random_value(length))
        }
    }
</script>

