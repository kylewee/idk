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
<h3 class="page-title"><?php echo TEXT_LOGS . ' <i class="fa fa-angle-right"></i> ' . strtoupper($log_type) ?></h3>

<?php require(component_path('logs/filters')) ?>

<div class="row">
  <div class="col-md-12">
    <div id="log_listing"></div>
  </div>
</div>

<?php echo input_hidden_tag('listing_order_by','date_added desc') ?>

<script>
  function load_items_listing(listing_container,page,search_keywords)
  {      
    $('#'+listing_container).append('<div class="data_listing_processing"></div>');
    $('#'+listing_container).css("opacity", 0.5);

    var filters = $('#log_filters').serializeArray();

          $('#' + listing_container).load('<?php echo url_for("logs/view", 'type=' . $log_type . '&action=listing') ?>', {page: page, filters: filters, order_by: $('#listing_order_by').val()},
                  function (response, status, xhr)
                  {
                      if (status == "error")
                      {
                          $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
                      }

                      $('#' + listing_container).css("opacity", 1);

                      appHandleUniformInListing()
                      
                      $('.listing_order').click(function(){
                            if($(this).hasClass('listing_order_desc'))
                            {
                                $('#listing_order_by').val($(this).attr('data_order_by')+' asc')                                
                                $(this).removeClass('listing_order_desc').addClass('listing_order_asc')
                            }
                            else
                            {
                                $('#listing_order_by').val($(this).attr('data_order_by')+' desc')                                
                                $(this).removeClass('listing_order_asc').addClass('listing_order_desc')
                            }
                            
                            load_items_listing('log_listing', 1)
                      })
                  }
          );
      }

      function reset_date_rane_filter(class_name)
      {
          $('.' + class_name + ' [name=from]').val('')
          $('.' + class_name + ' [name=to]').val('')

          load_items_listing('log_listing', 1)
      }


      $(function ()
      {
          load_items_listing('log_listing', 1, '');
          
          $('#log_filters').submit(function(){
            load_items_listing('log_listing', 1, '');
            return false;
          })
          
          $('#search').on('search',function(){
              if($(this).val()=='')
              {
                  load_items_listing('log_listing', 1)
              }
          })
          
          $('#sql_errors').change(function(){
              load_items_listing('log_listing', 1)
          })
          
          $('.btn-refresh').click(function(){
              load_items_listing('log_listing', 1)
          })
          
          $('.btn-reset-log').click(function(){
              
              $('#log_listing').append('<div class="data_listing_processing"></div>');
              $('#log_listing').css("opacity", 0.5);
    
              $.ajax({
                  url: $(this).attr('href')
              }).done(function(){                  
                  load_items_listing('log_listing', 1)
              })
              
              return false;
          })
      });


</script> 