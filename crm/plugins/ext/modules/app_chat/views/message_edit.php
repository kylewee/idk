
<div class="chat-msg-info " >
    <div>
        <?= form_tag('edit_msg',url_for('ext/app_chat/message_edit','id=' . $message_info['id'] . '&action=save&assigned_to=' . _GET('assigned_to'))) ?>

        <div class="chat-msg-text" id="chat_message_update_text" contenteditable="true"><div><?= $message_info['message']  ?></div><div>&nbsp;</div></div>
        <br>
        <?= submit_tag(TEXT_SAVE) ?>
        </form>
    </div>
</div>

<script>
    $('#edit_msg').submit(function(){
        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            data: {chat_message: $('#chat_message_update_text').html()}
        }).done(function(){
            $('#chat_msg_item_<?= $message_info['id'] ?>').load(url_for('ext/app_chat/message_edit','id=<?= $message_info['id'] . '&assigned_to=' . _GET('assigned_to') ?>&action=refresh'),function(){
                App.initFancybox()                            
                new ClipboardJS('.btn-chat-copy-text');
                initChatReply()
            })
        })
        
        $.fancybox.close()
        
        return false;
    })
</script>