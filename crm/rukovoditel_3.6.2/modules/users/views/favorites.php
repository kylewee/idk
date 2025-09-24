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

<h3 class="page-title" style="float:left"><?php echo TEXT_FAVORITES ?></h3>

<?php echo '<a style="float:right" href="' . url_for("users/favorites",'action=reset') . '" class="btn btn-default" onClick="return confirm(\'' . TEXT_ARE_YOU_SURE. '\')">' . TEXT_CLEAR. '</a>' ?>

<div class="row">
  <div class="col-md-12">
    <div id="favorites_listing"></div>
  </div>
</div>


<script>
  function load_items_listing(listing_container,page,search_keywords)
  {      
    $('#'+listing_container).append('<div class="data_listing_processing"></div>');
    $('#'+listing_container).css("opacity", 0.5);
    
    $('#'+listing_container).load('<?php echo url_for("users/favorites",'action=get_listing') ?>',{page:page},
      function(response, status, xhr) {
        if (status == "error") {                                 
           $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
        }
        
        $('#'+listing_container).css("opacity", 1);

        appHandleUniformInListing()

        handle_itmes_select(listing_container)
                                                                                                                    
      }
    );
  }

  function handle_itmes_select(listing_container)
  {  
    $('.favorite-icon').click(function(){
        if($(this).hasClass('active'))
        {
            $(this).removeClass('active')  
            $('.fa',this).removeClass('fa-star').addClass('fa-star-o')
            that = $(this)
           
           
           $.ajax({
               url:url_for('items/favorites','action=favorites_remove&path='+$(this).attr('data_path'))
           }).done(function(){
               favorites_render_dropdown()
               
               that.parents('tr').hide()  
               
               current_page = that.attr('data_page')
               
               load_items_listing('favorites_listing',current_page,''); 
           })
        }        

        return false;

    })
  }  


  $(function() {     
    load_items_listing('favorites_listing',1,'');                                                                         
  });
  
    
</script> 
