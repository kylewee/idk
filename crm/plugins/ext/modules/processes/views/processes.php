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

<h3 class="page-title"><?php echo TEXT_EXT_PROCESSES ?></h3>

<p><?php echo TEXT_EXT_PROCESSES_DESCRIPTION ?></p>

<div class="row">
  <div class="col-md-7">
		<?php 
			echo button_tag(TEXT_EXT_BUTTON_ADD_PROCESS,url_for('ext/processes/form')) . ' ' .
					 button_tag(TEXT_EXT_BUTTONS_GROUPS,url_for('ext/processes/buttons_groups'),false,array('class'=>'btn btn-default')) . ' ' .
					 button_tag('<i class="fa fa-sitemap"></i> ' . TEXT_FLOWCHART,url_for('ext/processes/processes_flowchart'),false,array('class'=>'btn btn-default')) 
		?>
  </div>
  <div class="col-md-2">
      <?= form_tag('processes_search_form',url_for('ext/processes/processes','action=set_processes_search_filter')) . input_search_tag('keywords', $processes_search_filter) . '</form>' ?>
  </div>
  <div class="col-md-3">
    <?php echo form_tag('processes_filter_form',url_for('ext/processes/processes','action=set_processes_filter')) ?>
      <?php echo select_tag('processes_filter',entities::get_choices_with_empty(),$processes_filter,array('class'=>'form-control  chosen-select','onChange'=>'this.form.submit()')) ?>
    </form>
  </div>
</div>  

<div class="row">
    <div class="col-md-12">
        <div id="process_buttons_listing"></div>
    </div>
</div>

<script>
    function load_items_listing(listing_container, page, search_keywords)
    {
        $('#' + listing_container).append('<div class="data_listing_processing"></div>');
        $('#' + listing_container).css("opacity", 0.5);
        
        $('#' + listing_container).load('<?php echo url_for("ext/processes/processes", 'action=listing') ?>', {page: page},
                function (response, status, xhr)
                {
                    if (status == "error")
                    {
                        $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
                    }

                    $('#' + listing_container).css("opacity", 1);

                    appHandleUniformInListing()
                }
        );
    }


    $(function ()
    {
        load_items_listing('process_buttons_listing', 1, '');
        
        $('#processes_search_form').submit(function(){
            
            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize()
            }).done(function(){
                load_items_listing('process_buttons_listing', 1, '');
            })
            
            return false;
        })
    });


</script> 

