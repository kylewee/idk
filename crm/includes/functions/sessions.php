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
class rkSession implements SessionHandlerInterface
{

    private $link;

    public function open($savePath, $sessionName):bool
    {
        return true;
    }

    public function close():bool
    {
        return true;
    }

    public function read($key):string
    {
        $value_query = db_query("select value from app_sessions where sesskey = '" . db_input($key) . "' and expiry > '" . time() . "'");
        $value = db_fetch_array($value_query);

        if(isset($value['value']))
        {
            return $value['value'];
        }
        else
        {
            return '';
        }
    }

    public function write($key, $val):bool
    {
        global $SESS_LIFE;

        $expiry = time() + $SESS_LIFE;
        $value = $val;

        $check_query = db_query("select count(*) as total from app_sessions where sesskey = '" . db_input($key) . "'");
        $check = db_fetch_array($check_query);

        if($check['total'] > 0)
        {
            db_query("update app_sessions set expiry = '" . db_input($expiry) . "', value = '" . db_input($value) . "' where sesskey = '" . db_input($key) . "'");
        }
        else
        {
            db_query("insert into app_sessions values ('" . db_input($key) . "', '" . db_input($expiry) . "', '" . db_input($value) . "')");
        }

        return true;
    }

    public function destroy($key):bool
    {
        db_query("delete from app_sessions where sesskey = '" . db_input($key) . "'");

        return true;
    }

    public function gc($maxlifetime):int|false
    {
        db_query("delete from app_sessions where expiry < '" . (time() - $maxlifetime) . "'");

        return true;
    }
}

if(STORE_SESSIONS == 'mysql')
{
    if(!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime'))
    {
        $SESS_LIFE = 1440;
    }

    register_shutdown_function('session_write_close');

    $handler = new rkSession();
    session_set_save_handler($handler, true);
}

function app_session_table_reset()
{
    db_query("delete from app_sessions where expiry < '" . strtotime("-1 day") . "'");
}

function app_session_start()
{
    global $_GET, $_POST, $_COOKIE;

    $sane_session_id = true;

    if(isset($_GET[app_session_name()]))
    {
        if(preg_match('/^[a-zA-Z0-9]+$/', $_GET[app_session_name()]) == false)
        {
            unset($_GET[app_session_name()]);

            $sane_session_id = false;
        }
    }
    elseif(isset($_POST[app_session_name()]))
    {
        if(preg_match('/^[a-zA-Z0-9]+$/', $_POST[app_session_name()]) == false)
        {
            unset($_POST[app_session_name()]);

            $sane_session_id = false;
        }
    }
    elseif(isset($_COOKIE[app_session_name()]))
    {
        if(preg_match('/^[a-zA-Z0-9]+$/', $_COOKIE[app_session_name()]) == false)
        {
            $session_data = session_get_cookie_params();

            setcookie(app_session_name(), '', time() - 42000, $session_data['path'], $session_data['domain']);

            $sane_session_id = false;
        }
    }

    if($sane_session_id == false)
    {
        //put redirect here
    }

    return session_start();
}

function app_session_register($variable, $value = null)
{
    global $session_started;

    if($session_started == true)
    {
        if(isset($GLOBALS[$variable]))
        {
            $_SESSION[$variable] = & $GLOBALS[$variable];
        }
        else
        {
            $_SESSION[$variable] = $value;
        }
    }

    return false;
}

function app_session_is_registered($variable)
{
    return isset($_SESSION) && array_key_exists($variable, $_SESSION);
}

function app_session_unregister($variable)
{
    unset($_SESSION[$variable]);
}

function app_session_id($sessid = '')
{
    if(!empty($sessid))
    {
        return session_id($sessid);
    }
    else
    {
        return session_id();
    }
}

function app_session_name($name = '')
{
    if(!empty($name))
    {
        return session_name($name);
    }
    else
    {
        return session_name();
    }
}

function app_session_close()
{
    return session_write_close();
}

function app_session_destroy()
{
    return session_destroy();
}

function app_session_save_path($path = '')
{
    if(!empty($path))
    {
        return session_save_path($path);
    }
    else
    {
        return session_save_path();
    }
}

