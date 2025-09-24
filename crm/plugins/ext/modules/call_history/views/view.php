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

<h3 class="page-title"><?php echo TEXT_EXT_CALL_HISTORY ?></h3>

<?php require(component_path('ext/call_history/filters')) ?>

<div class="row">
    <div class="col-md-12">
        <div id="call_history_listing"></div>
    </div>
</div>

<script>
  function load_items_listing(listing_container,page)
  {      
    $('#'+listing_container).append('<div class="data_listing_processing"></div>');
    $('#'+listing_container).css("opacity", 0.5);

    var filters = $('#call_history_filters').serializeArray();
    
    $('#'+listing_container).load('<?php echo url_for("ext/call_history/view",'action=listing') ?>',{page:page,filters:filters},
      function(response, status, xhr) {
        if (status == "error") {                                 
           $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
        }
        
        $('#'+listing_container).css("opacity", 1);

        appHandleUniformInListing()   
        
        //star
        $('.btn-action-star').click(function(){
            let is_star = 0
            
            if($(this).hasClass('mail-star-active'))
            {
                $(this).removeClass('mail-star-active')                
            }
            else
            {
                $(this).addClass('mail-star-active')
                is_star = 1
            }
            
            $.ajax({
                    type:'POST',
                    url: url_for('ext/call_history/view','action=set_star'),
                    data:{
                        is_star: is_star,
                        id: $(this).attr('data_id')
                    }
                })
        })
        
        //delete
        $('.btn-action-delete').click(function(){
            $(this).parents('tr').addClass('row-selected')
            
            setTimeout(()=>{
                if(confirm(i18n['TEXT_ARE_YOU_SURE']))
                {
                    $.ajax({
                        type:'POST',
                        url: url_for('ext/call_history/view','action=delete'),
                        data:{                            
                            id: $(this).attr('data_id')
                        }
                    })
                    
                    load_items_listing('call_history_listing',page);         
                }
                else
                {
                    $(this).parents('tr').removeClass('row-selected')
                }
            },100)            
        })
                
        //play
        $('.play-circle-action').click(function(){
            $(this).addClass('play-circle-off')
        })
        
      }
    );
  }


  $(function() {     
    load_items_listing('call_history_listing',1);         
    
  });
  
    
</script> 