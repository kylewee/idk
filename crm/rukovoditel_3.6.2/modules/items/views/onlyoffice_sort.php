
<?php echo ajax_modal_template_header(TEXT_SORT  . ': '. $app_fields_cache[$current_entity_id][$field_id]['name']) ?>

<?php echo form_tag('choices_form',url_for('items/onlyoffice_sort','path=' . $app_path . '&action=sort&field_id=' . $field_id)) ?>
<div class="modal-body ajax-modal-width-1100">         
    <div class="dd" id="choices_sort">
        <?php  
        
        $html = '<ol class="dd-list">';
        
        $files_query = db_query("select * from app_onlyoffice_files where find_in_set(id,'" . $item_info['field_' . $field_id] . "') and field_id='" . db_input($field_id) . "' order by sort_order, id", false);
        while ($file = db_fetch_array($files_query))
        {
            $fileinfo = onlyoffice::get_file_info($file);
            $img = '<img src="' . url_for_file($fileinfo['icon']) . '" widht="16"> ';
            $html .= '
                    <li class="dd-item" data-id="' . $file['id'] . '">
                        <div class="dd-handle" style="height: auto; max-height: 60px; overflow:hidden;">
                            ' . $img  . $file['filename'] . '
                        </div>
                     </li>
                ';
        }
        $html .= '</ol>';
        echo $html;
        ?>
    </div>
</div>

<?php echo input_hidden_tag('choices_sorted') ?> 
<?php echo ajax_modal_template_footer() ?>
</form>

<script>
$(function(){
  $('#choices_sort').nestable({
      group: 1,
      maxDepth:1,
  }).on('change',function(e){
    output = $(this).nestable('serialize');
    
    if (window.JSON) 
    {
      output = window.JSON.stringify(output);
      $('#choices_sorted').val(output);
    } 
    else 
    {
      alert('JSON browser support required!');      
    }    
  })
})

</script>

