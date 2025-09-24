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

<form class="form-inline" name="call_history_filters" id="call_history_filters" action="" style="margin-bottom: 10px">

    <div class="form-group">
        <div class="input-group input-large datepicker input-daterange daterange-filter">					
            <span class="input-group-addon">
                <i class="fa fa-calendar"></i>
            </span>
            <?php echo input_tag('from', '', array('class' => 'form-control', 'placeholder' => TEXT_DATE_FROM)) ?>
            <span class="input-group-addon">
                <i style="cursor:pointer" class="fa fa-refresh" aria-hidden="true" title="<?php echo TEXT_EXT_RESET ?>" onClick="reset_date_rane_filter('daterange-filter')"></i>
            </span>
            <?php echo input_tag('to', '', array('class' => 'form-control', 'placeholder' => TEXT_DATE_TO)) ?>			
        </div>		
    </div>


    <div class="form-group">	
        <div class="input-group input-medium ">					            
            <span class="input-group-addon">
                <i  class="fa fa-exchange" aria-hidden="true" title="<?php echo TEXT_TYPE ?>" ></i>
            </span>	
            <?php echo select_tag('direction', [''=>'','in'=>TEXT_EXT_INCOMING_CALL,'out'=>TEXT_EXT_OUTGOING_CALL,'stared'=>TEXT_EXT_STARRED],'', array('class' => 'form-control')) ?>
        </div>		
    </div> 
    
    <div class="form-group">	
        <div class="input-group input-xmedium ">					            
            <span class="input-group-addon">
                <i  class="fa fa-phone" aria-hidden="true" title="<?php echo TEXT_STATUS ?>" ></i>
            </span>	
            <?php echo select_tag('duration', [''=>'','1'=>TEXT_EXT_ANSWERED,'0'=>TEXT_EXT_NOT_ANSWERED,'unheard'=>TEXT_EXT_UNLISTENED],'', array('class' => 'form-control')) ?>
        </div>		
    </div>

<?php $search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';?>  
    
    <div class="form-group">	
        <div class="input-group input-medium ">					
            <?php echo input_tag('search', $search, array('class' => 'form-control', 'placeholder' => TEXT_SEARCH,'type'=>'search')) ?>
            <span class="input-group-addon" style="cursor:pointer" onClick="load_items_listing('call_history_listing', 1)">
                <i  class="fa fa-search" aria-hidden="true" title="<?php echo TEXT_SEARCH ?>" ></i>
            </span>      
            
            <input type="submit" style="display:none">
        </div>		
    </div>    
    
</form>

<script>
$(function(){
    $('#call_history_filters .form-control:not(#search)').change(function(){            
        load_items_listing('call_history_listing',1)
    })
    
    $('#call_history_filters').on('submit',function(){             
        load_items_listing('call_history_listing',1)
        return false
    })
    
    $('#search').on('input', function(e) {        
        if(this.value.length==0)
        {
            load_items_listing('call_history_listing',1)
        }
    })        
})

function reset_date_rane_filter(class_name)
{	
    $('.'+class_name+' .form-control').val('')	

    load_items_listing('call_history_listing',1)
}
</script>

