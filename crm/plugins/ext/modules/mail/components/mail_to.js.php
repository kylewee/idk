<style>
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

	#select2-mail_to-results{
		display:none;
	}
	
	#select2-mail_to-results li:first-child{
		display:none;
	}
</style>

<script>

    var input_cliced = false
    var current_account_id = $('#accounts_id').val()
    
    

  $(function() { 
    
    $('#mail_form').validate({
      ignore:'',
      rules:{
      	"body": { 
            required: function(element){
              CKEDITOR_holders["body"].updateElement();                 
              return true;             
            }
      	},
      },
        submitHandler: function(form){
            app_prepare_modal_action_loading(form)
            
            if($(form).attr('is_ajax') && $(form).attr('is_ajax')==1)
            {
                $.ajax({
                    type: 'POST',
                    url: $(form).attr('action'),
                    data: $(form).serializeArray()
                }).done(function(msg){
                    //alert(msg)
                    $('.form-body',form).html(msg)
                    $('.primary-modal-action-loading').hide()
                    $(window).resize()
                    
                    if(msg.search('alert-success')!=-1)
                    {
                        setTimeout(function(){
                            $('#ajax-modal').modal('toggle')
                        },1500)
                    }
                                        
                    //update related email box
                    if($('#redirect_to').length!=-1 && $('#redirect_to').val().search('item_info_mail_')!=-1)
                    {
                        $('#mail_related_'+$('#redirect_to').val().replace('item_info_mail_','')).load(url_for('items/render_related_mail','path='+$(form).attr('path')+'&redirect_to='+$('#redirect_to').val()))
                    }
                    else if($('#redirect_to').length!=-1 && $('#redirect_to').val().search('listing_')!=-1)
                    {                        
                        let val = $('#redirect_to').val().split('_')
                        let listing_id = 'entity_items_listing'+val[1]+'_'+val[2]
                        let page = val[3]
                        load_items_listing(listing_id,page)
                        
                    }
                })
                
                return false
            }
            else
            {
                return true
            }
        }
			
    }); 
    
    //hide body error msg
    CKEDITOR_holders["body"].on('change', function() { 
        $('#body-error').hide();
    });

//apply select2    
    var $mail_to = $("#mail_to").select2({
      tags: true,
      width: '100%',
      selectOnClose: true,
      dropdownParent: $('#ajax-modal'),
      tokenSeparators: [',', ' '],
      "language":{
        "noResults" : function () { return ''; },
    		"searching" : function () { return ''; }
      },            
      ajax: {
        url: '<?php echo url_for('ext/mail/accounts','action=search_contacts') ?>',
        dataType: 'json',
        delay: 0,
        processResults: function (data) {
         	//console.log(data);
         	if(data['results'].length>=1)
         	{
           	$('#select2-mail_to-results').show()
          }
         	else
         	{
         		$('#select2-mail_to-results').hide()
          } 
          return data;
        },
      },
                           
    });

$('#mail_to').on('select2:opening', function (e) {
  $('#mail_to-error').hide();
});

//check email once it's entered
    var $search_field = $('.select2-search__field');
                       
    $('#mail_to').on('select2:select', function (e) {
      var data = e.params.data;
      email = data.id;
      email_list = $mail_to.val();
      
      if(email.indexOf('<')>-1)
      {
      	email = email.substr(email.indexOf('<') + 1).slice(0, -1);
      }
      
      if(!is_valid_email(email))
      {        
        $('#mail_to-error').html('<?php echo TEXT_ERROR_REQUIRED_EMAIL ?>')
        
        var index = email_list.indexOf(email);
        
        if (index > -1) {
        	email_list.splice(index, 1);
        }
        
        $mail_to.val(email_list).trigger("change");

        $search_field.val(email).css('width', ($search_field.val().length+1*0.75)+'em');                               
      } 
      
       
      $search_field.keydown(function(){      	
      	$('#mail_to-error').hide();
      }) 
                       
      //console.log(data.id);
      //console.log($mail_to.val())
  	});
    
//focus input field
    $('#ajax-modal').on('shown.bs.modal', function () {
        mail_form_focus()
    })
    
    
    
    $('#accounts_id').change(function(){        
        set_user_signature()
    })
        
    setTimeout(function(){
        set_user_signature()        
    },500)   
    
    
    $("#mail_to").change(function (e) {
        $("#mail_to-error").hide();
    });
    
    
    $('.mail-template-button').click(function(){
        data = $(this).data();        
        accounts_id = $('#accounts_id').val()
        fetch(url_for('ext/mail/templates','action=apply&id='+data.id+'&accounts_id='+accounts_id)).then((response) => {
            return response.json();
        }).then((data) => {
            //console.log($('#subject').val().length);
            
            if($('#subject').attr('type')!='hidden')
            {            
                $('#subject').val(data.subject)
                CKEDITOR.instances.body.setData(data.body)
            }
            else
            {
               CKEDITOR.instances.body.insertHtml(data.body) 
            }
            
                        
        });
    })
		                                                                                                                       
  });
  
  function mail_form_focus()
  {
        if($('#mail_form').attr('is_ajax')==1)
        {
            $("#subject").focus()
        }
        else
        {
            $("#mail_to").select2('focus')    	    
        }
  }
  
  
  function set_user_signature()
  {
      
      
      
    CKEDITOR_holders["body"].updateElement();
                        
    account_id = $('#accounts_id').val()
        
    if($('#signature_'+account_id).val().length==0) return false;
    
    signature = '<br><br>'+$('#signature_'+account_id).val()
    current_signature = $('#signature_'+current_account_id).val()
        
    if(strip_tags($('#body').val())==strip_tags(current_signature))
    {        
        CKEDITOR.instances.body.setData(signature);
    }
    else
    {
        CKEDITOR.instances.body.insertHtml(signature);
    }

    current_account_id = account_id
    
    //move cursor to the start
    var element = CKEDITOR.instances.body.document.getBody()
    var range = CKEDITOR.instances.body.createRange();
    if(range) {
       range.moveToElementEditablePosition(element, false);
       range.select();
    }
    
  }
</script>