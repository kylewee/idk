<?php

class who_is_online
{
    static function set($user_id = false)
    {
        global $app_user;
        
        $user_id = $user_id ? $user_id : $app_user['id'];
                        
        $check_query = db_query("select * from app_who_is_online where users_id={$user_id}");
        if($check = db_fetch_array($check_query))
        {
            db_query("update app_who_is_online set date_updated=" . time() . " where users_id='" . $user_id . "'");
        }
        else
        {
            $sql_data = array(
                'users_id' => db_prepare_input($user_id),
                'date_updated' => time(),
            );

            db_perform('app_who_is_online', $sql_data);
        }
    }
    
    static function remove(int $user_id)
    {
        db_delete_row('app_who_is_online', $user_id, 'users_id');
    }
    
    static function count_online()
    {
        $count_query = db_query("select count(*) as total from app_who_is_online where date_updated>=" . (time()-(CFG_WHO_IS_ONLINE_INTERVAL*60)) );
        $count = db_fetch_array($count_query);
        
        return $count['total'];
    }
    
    static function render_js()
    {
        if(!CFG_WHO_IS_ONLINE_STATUS or !CFG_WHO_IS_ONLINE_INTERVAL) return '';
        
        $itnerval = CFG_WHO_IS_ONLINE_INTERVAL*60*1000;
        
        $html = '
            <script>
                 $(function(){
                    setInterval(function(){
                        $.ajax({url: "' . url_for('dashboard/dashboard','action=who_is_online') . '"})
                    },' . $itnerval . ');                                                                   
                 }); 
            </script>
            ';
        
        self::set();
        
        return $html;
    }
}
