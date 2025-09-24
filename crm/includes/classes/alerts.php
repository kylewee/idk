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

class alerts
{
    public $messages;
    
    function __construct()
    {
        $this->messages = array();
    }

    function count()
    {
        return count($this->messages);
    }

    function add($message, $type = '')
    {
        $class = '';
        switch($type)
        {
            case 'error':
                $class = 'alert-danger';
                break;
            case 'warning':
                $class = 'alert-warning';
                break;
            case 'success':
                $class = 'alert-success';
                break;
            default:
                $class = 'alert-info';
                break;
        }

        $this->messages[] = array('params' => 'class="alert ' . $class . '"', 'text' => $message);
    }

    function output()
    {
        if(count($this->messages) == 0)
            return '';


        $output = array();
        foreach($this->messages as $v)
        {
            $output[] = '<div ' . $v['params'] . '><button type="button" class="close" data-dismiss="alert">&times;</button>' . $v['text'] . '</div>';
        }

        //reset messages
        $this->messages = array();

        return implode("\n", $output);
    }

}
