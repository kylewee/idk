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

class num2str
{

    public $data;

    function __construct()
    {
        $path = 'includes/languages/num2str/';
        if($handle = opendir($path))
        {
            while(false !== ($entry = readdir($handle)))
            {
                if($entry != "." && $entry != "..")
                {
                    require('includes/languages/num2str/' . $entry);

                    $this->data[str_replace('.php', '', $entry)] = $data;
                }
            }
            closedir($handle);
        }
    }
    

    function prepare($text, $item)
    {
        foreach($this->data as $code => $data)
        {            
            if(preg_match_all('/num2str_' . $code . '\({#(\w+):[^}]*}\)|num2str_' . $code . '\(\[(\d+)\]\)/', $text, $matches))
            {
                //echo '<pre>';
                //print_r($matches);

                foreach($matches[1] as $matches_key => $filed_id)
                {
                    $number = '';
                    
                    $filed_id = (strlen($matches[2][$matches_key]) ? $matches[2][$matches_key] : $filed_id);
                    
                    
                    $field_query = db_query("select * from app_fields where id='" . (int)$filed_id . "'");
                    if($field = db_fetch_array($field_query))
                    {                                                
                        $value = $item['field_' . $field['id']]??'';
                        
                        $output_options = array('class' => $field['type'],
                            'value' => $value,
                            'field' => $field,
                            'item' => $item,
                            'is_export' => true,                           
                            'path' => $field['entities_id']);
                        
                        $number = trim(strip_tags(fields_types::output($output_options)));                                               
                    }
                    
                    
                    if(!strlen($number))
                    {
                        $number = 0;
                    }
                    
                    $minus = '';
                    if(substr($number,0,1)=='-')
                    {
                        $number = substr($number,1);
                        $minus = '- '; 
                    }
                                        
                    //echo $matches[0][$matches_key] . ' - ' . $number . '<br>' . $this->convert($code, $number) . '<br><br>';

                    $text = str_replace($matches[0][$matches_key], $minus . $this->convert($code, $number), $text);
                }
            }
        }

        return $text;
    }

    function convert($code, $num, $add_unit = true)
    {
        $nul = $this->data[$code]['nul'];
        $ten = $this->data[$code]['ten'];
        $a20 = $this->data[$code]['a20'];
        $tens = $this->data[$code]['tens'];
        $hundred = $this->data[$code]['hundred'];
        $unit = $this->data[$code]['unit'];

        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));

        $out = array();

        if(intval($rub) > 0)
        {
            foreach(str_split($rub, 3) as $uk => $v)
            { // by 3 symbols
                if(!intval($v))
                    continue;

                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];

                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));

                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if($i2 > 1)
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3];# 20-99
                else
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3];# 10-19 | 1-9
                // units without rub & kop
                if($uk > 1)
                    $out[] = $this->morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
            } //foreach
        }
        else
            $out[] = $nul;

        if($add_unit)
        {
            $out[] = $this->morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub

            if(strlen($unit[0][0]))
            {
                $out[] = $kop . ' ' . $this->morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
            }
        }

        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

    function morph($n, $f1, $f2, $f5)
    {
        $n = abs(intval($n)) % 100;
        if($n > 10 && $n < 20)
            return $f5;
        $n = $n % 10;
        if($n > 1 && $n < 5)
            return $f2;
        if($n == 1)
            return $f1;
        return $f5;
    }

}
