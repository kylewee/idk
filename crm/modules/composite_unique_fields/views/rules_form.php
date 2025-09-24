
<?php echo ajax_modal_template_header(TEXT_RULE_FOR_FIELD) ?>

<?php echo form_tag('rules_form', url_for('composite_unique_fields/rules', 'action=save&entities_id=' . $_GET['entities_id'] . (isset($_GET['id']) ? '&id=' . $_GET['id'] : '')), array('class' => 'form-horizontal')) ?>
<div class="modal-body">
    <div class="form-body ajax-modal-width-790">

        <div class="form-group">
            <label class="col-md-3 control-label" for="is_active"><?php echo TEXT_IS_ACTIVE ?></label>
            <div class="col-md-9">	
                <p class="form-control-static"><?php echo input_checkbox_tag('is_active', $obj['is_active'], array('checked' => ($obj['is_active'] == 1 ? 'checked' : ''))) ?></p>
            </div>			
        </div>
        
        

        <?php
        $choices = array([''=>'']);
        $fields_query = fields::get_query(_GET('entities_id'), " and f.type in (" . db_input_in(composite_unique_fields::allowed_field_types()) . ")");
        while($v = db_fetch_array($fields_query))
        {
            $choices[$v['id']] = fields_types::get_option($v['type'], 'name', $v['name']);
        }
        ?>

        <div class="form-group">
            <label class="col-md-3 control-label" for="name"><?php echo TEXT_FIELD . ' 1' ?></label>
            <div class="col-md-9">	
                <?php echo select_tag('field_1', $choices, $obj['field_1'], array('class' => 'form-control chosen-select required ',)) ?>  	  
            </div>			
        </div>  

        <div class="form-group">
            <label class="col-md-3 control-label" for="name"><?php echo TEXT_FIELD . ' 2' ?></label>
            <div class="col-md-9">	
                <?php echo select_tag('field_2', $choices, $obj['field_2'], array('class' => 'form-control chosen-select required ',)) ?>  	  
            </div>			
        </div> 
        
        <?php if($app_entities_cache[$_GET['entities_id']]['parent_id']>0): ?>
        <div class="form-group">
            <label class="col-md-3 control-label" for="is_unique_for_parent"><?php echo TEXT_UNIQUE_FOR_EACH_PARENT_RECORD ?></label>
            <div class="col-md-9">	
                <p class="form-control-static"><?php echo input_checkbox_tag('is_unique_for_parent', 1, array('checked' => ($obj['is_unique_for_parent'] == 1 ? 'checked' : ''))) ?></p>
            </div>			
        </div>
        <?php endif ?>

        <div class="form-group">
            <label class="col-md-3 control-label" for="sort_order"><?php echo TEXT_MESSAGE_TEXT ?></label>
            <div class="col-md-9">	
                <?php echo textarea_tag('message', $obj['message'], array('class' => 'form-control required editor','toolbar'=>'small')) ?>
                <?= tooltip_text(TEXT_COMPOSITE_UNIQUE_MESSAGE_TIP) ?>
            </div>			
        </div>  
        
    </div>
</div>

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
    $(function ()
    {
        $('#rules_form').validate({
            ignore: '',
            submitHandler: function(form){
                app_prepare_modal_action_loading(form)
                return true;
            },
            rules:{
                "message": { 
                required: function(element){
                    CKEDITOR_holders["message"].updateElement();              
                    return true;             
                }
              },
            }
        });
    });
</script>   


