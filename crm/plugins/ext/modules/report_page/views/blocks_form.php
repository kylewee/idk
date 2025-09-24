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

<?php echo form_tag('report_form', url_for('ext/report_page/blocks', 'report_id=' . _GET('report_id') . '&action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '')), array('class' => 'form-horizontal')) ?>

<div class="modal-body ajax-modal-width-1100">
    <div class="form-body">

        <?php
        $choices = [];
        if($report_page['entities_id']>0)
        {
            $choices['field'] = TEXT_FIELD;
            
            if(entities::has_subentities($report_page['entities_id']))
            {
                $choices['nested_entity'] = TEXT_SUB_ENTITY;
            }
        }
        $choices['table'] = TEXT_TABLE . ' (' . TEXT_MYSQL_QUERY. ')';
        $choices['php'] = TEXT_PHP_CODE;
        $choices['html'] = TEXT_HTML_CODE;
        
        
        ?>
        <div class="form-group">
            <label class="col-md-3 control-label" ><?php echo TEXT_TYPE ?></label>
            <div class="col-md-9"><?php echo select_tag('block_type', $choices, $obj['block_type'], array('class' => 'form-control input-large required')) ?>
            </div>			
        </div>
        
        <div class="form-group" form_display_rules="block_type:!field">
            <label class="col-md-3 control-label" ><?php echo TEXT_NAME ?></label>
            <div class="col-md-9"><?php echo input_tag('name', $obj['name'], array('class' => 'form-control input-large required')) ?>
            </div>			
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_SORT_ORDER ?></label>
            <div class="col-md-9"><?php echo input_tag('sort_order', $obj['sort_order'], ['class' => 'form-control input-xsmall']) ?></div>			
        </div>

        <div id="block_settings"></div>

    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>

</form> 

<?php echo app_include_codemirror(['javascript','sql','php','clike','css','xml']) ?>

<script>
    $(function ()
    {
        $('#report_form').validate({
            submitHandler: function (form)
            {
                app_prepare_modal_action_loading(form)
                return true;
            }
        });

        block_settings();

        $('#block_type').change(function ()
        {
            block_settings();
        })
         
 

    });


    function block_settings()
    {
        block_type = $('#block_type').val();

        $('#block_settings').html('<div class="ajax-loading"></div>');

        $('#block_settings').load('<?php echo url_for("ext/report_page/blocks_settings", 'report_id=' . _GET('report_id')) ?>', {block_type: block_type, id: '<?php echo $obj["id"] ?>'}, function (response, status, xhr)
        {
            if (status == "error")
            {
                $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
            }
            else
            {
                appHandleUniform();
                
                $(".input-masked").each(function(){
                    $.mask.definitions["~"]="[,. ]";
                    $(this).mask($(this).attr("data-mask"));
                }) 

                jQuery(window).resize();
            }
        });

    }
    
    function field_settings()
    {
        field_id = $('#field_id').val();
      
        $('#field_settings')
                .html('<div class="ajax-loading"></div>')
                .load('<?php echo url_for("ext/report_page/field_settings", 'report_id=' . _GET('report_id')) ?>', {field_id: field_id, id: '<?php echo $obj["id"] ?>'}, function (response, status, xhr)
        {
            if (status == "error")
            {
                $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
            }
            else
            {
                appHandleUniform();

                jQuery(window).resize();
            }
        });
    }
    
    function nested_entity_settings()
    {              
        $('#nested_entity_settings')
                .html('<div class="ajax-loading"></div>')
                .load('<?php echo url_for("ext/report_page/nested_entity_settings", 'report_id=' . _GET('report_id')) ?>', {entity_id: $("#settings_entity_id").val(), id: '<?php echo $obj["id"] ?>'}, function (response, status, xhr)
        {
            if (status == "error")
            {
                $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
            }
            else
            {
                appHandleUniform();

                jQuery(window).resize();
            }
        });
    }
</script>