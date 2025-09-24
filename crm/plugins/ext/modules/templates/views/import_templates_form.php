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

<?php echo ajax_modal_template_header(TEXT_EXT_HEADING_TEMPLATE_IFNO) ?>

<?php echo form_tag('modal_form', url_for('ext/templates/import_templates', 'action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '')), array('class' => 'form-horizontal')) ?>

<div class="modal-body">
    <div class="form-body">


        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_GENERAL_INFO ?></a></li>   
            <li><a href="#configuration"  data-toggle="tab"><?php echo TEXT_SETTINGS ?></a></li>
            <li><a href="#import_by_url"  data-toggle="tab"><?php echo TEXT_EXT_IMPORT_BY_URL ?></a></li>      
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
                    <label class="col-md-3 control-label" for="entities_id"><?php echo TEXT_ENTITY ?></label>
                    <div class="col-md-9"><?php echo select_tag('entities_id', entities::get_choices(), $obj['entities_id'], array('class' => 'form-control input-large required')) ?>
                    </div>			
                </div>  

                <div id="sub_entities_list"></div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('name', $obj['name'], array('class' => 'form-control input-large required')) ?>
                    </div>			
                </div> 

                <div class="form-group">
                    <label class="col-md-3 control-label" for="users_groups"><?php echo TEXT_USERS_GROUPS ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('users_groups[]', access_groups::get_choices(), $obj['users_groups'], ['class' => 'form-control input-xlarge chosen-select', 'multiple' => 'multiple']) ?>      
                    </div>			
                </div> 

                <div class="form-group">
                    <label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_SORT_ORDER ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('sort_order', $obj['sort_order'], array('class' => 'form-control input-xsmall')) ?>
                    </div>			
                </div>  

            </div>

            <div class="tab-pane fade" id="configuration">

                <div id="fields_configuration"></div>

            </div>

            <div class="tab-pane fade" id="import_by_url">

                <p><?php echo TEXT_EXT_XML_FILE_PATH_INFO . '<br><code>' . DIR_FS_CATALOG . 'cron/xls_import.php</code>' ?></p>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="filepath"><?php echo TEXT_EXT_FILE_PATH ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('filepath', $obj['filepath'], ['class' => 'form-control']) ?>      
                    </div>			
                </div> 
<?php   
$choices = [
    'xls' => 'xls',
    'csv' => 'csv/txt'
];
?>
                <div class="form-group">
                    <label class="col-md-3 control-label" ><?php echo TEXT_TYPE ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('filetype', $choices, $obj['filetype'],['class' => 'form-control input-small']) ?>      
                    </div>			
                </div> 
                
<?php
$choices = [
    'UTF-8' => 'UTF-8',
    'ISO-8859-2' => 'ISO-8859-2',
    'ISO-8859-5' => 'ISO-8859-5',
    'Windows-1250' => 'Windows-1250',
    'Windows-1251' => 'Windows-1251',
    'Windows-1252' => 'Windows-1252',   
    'KOI8-R' => 'KOI8-R',
    'KOI8-U' => 'KOI8-U',
];
?>
                <div class="form-group" form_display_rules="filetype:csv">
                    <label class="col-md-3 control-label" for="import_action"><?= TEXT_ENCODING ?></label>
                    <div class="col-md-9"><?= select_tag('file_encoding',$choices,$obj['file_encoding'],array('class'=>'form-control input-medium')) ?></div>			
                </div>
                
<?php
$choices = [
    ',' => ',',
    ';' => ';',
    'tab' => TEXT_TAB,
    'space' => TEXT_SPACE,
    'other' => TEXT_OTHER,
];

$text_delimiter = isset($choices[$obj['text_delimiter']]) ? $obj['text_delimiter'] : 'other'; 
?>
                <div class="form-group" form_display_rules="filetype:csv">
                    <label class="col-md-3 control-label" for="import_action"><?= TEXT_SEPARATOR ?></label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <?= select_tag('text_delimiter',$choices,$text_delimiter,array('class'=>'form-control input-medium')) ?>
                            <?= input_tag('text_delimiter_other',$obj['text_delimiter'],['class'=>'form-control input-small','form_display_rules'=>'text_delimiter:other']) ?>
                        </div>    
                    </div>			
                </div>                

<?php
$choices = [
    'import' => TEXT_ACTION_IMPORT_DATA,
    'update' => TEXT_ACTION_UPDATE_DATA,
    'update_import' => TEXT_ACTION_UPDATE_AND_IMPORT_DATA,
];
?>
                <div class="form-group">
                    <label class="col-md-3 control-label" for="import_action"><?= TEXT_ACTION ?></label>
                    <div class="col-md-9"><?= select_tag('import_action',$choices,$obj['import_action'],array('class'=>'form-control input-large')) ?></div>			
                </div>

<?php
$alphabet = range('A', 'Z');
$choices = [];
for($i=0;$i<26;$i++)
{
    $choices[$i] = $alphabet[$i] . ' ' . ($i+1);
}
?>
                <div class="form-group" form_display_rules="import_action:!import">
                    <label class="col-md-3 control-label" ><?= TEXT_UPDATE_BY_FIELD ?></label>
                    <div class="col-md-9"><?= select_tag('update_use_column',$choices,$obj['update_use_column'],array('class'=>'form-control input-small')) . tooltip_text(TEXT_USE_COLUMN) ?></div>			
                </div>
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="filepath"><?php echo TEXT_START_IMPORT_FROM_LINE ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('start_import_line', $obj['start_import_line'], ['class' => 'form-control input-xsmall','type'=>'number','min'=>1]) ?>      
                    </div>			
                </div> 

                                
                <div id="parent_items_choices"></div>
            </div>  
        </div> 

    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
    $(function ()
    {
        $('#modal_form').validate({ignore:'',
            submitHandler: function (form)
            {
                app_prepare_modal_action_loading(form)
                form.submit();
            }
        });


        load_entities_list();
        load_entity_parent_items_choices()

        //load entity fields
        $('#entities_id').change(function ()
        {
            load_entities_list()
            load_entity_parent_items_choices()
        })
        
        //reset custom delimiter
        $('#text_delimiter').change(function(){            
            $('#text_delimiter_other').val('')            
        })

    });


    function load_entities_list()
    {
        $('#sub_entities_list').html('<div class="ajax-loading"></div>');

        $('#sub_entities_list').load('<?php echo url_for("ext/templates/import_templates", "action=get_subentities") ?>', {id: '<?php echo $obj["id"] ?>', entities_id: $('#entities_id').val()}, function (response, status, xhr)
        {
            if (status == "error")
            {
                $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
            }
            else
            {
                appHandleUniform();

                load_fields_configuration();

                jQuery(window).resize();
            }
        });
    }

    function load_fields_configuration()
    {
        $('#fields_configuration').html('<div class="ajax-loading"></div>');

        $('#fields_configuration').load('<?php echo url_for("ext/templates/import_templates", "action=fields_configuration") ?>', {id: '<?php echo $obj["id"] ?>', entities_id: $('#entities_id').val(), multilevel_import: $('#multilevel_import').val()}, function (response, status, xhr)
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

    function load_entity_parent_items_choices()
    {
        $('#parent_items_choices').html('<div class="ajax-loading"></div>');

        $('#parent_items_choices').load('<?php echo url_for("ext/templates/import_templates", "action=get_parent_items_choices") ?>', {entities_id: $('#entities_id').val(), id: '<?php echo $obj["id"] ?>'}, function (response, status, xhr)
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