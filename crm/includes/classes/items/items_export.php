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

require(CFG_PATH_TO_PHPSPREADSHEET);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class items_export
{
    public $filename;
    
    private $template_filepath;
    
    function __construct($filename)
    {
        $this->filename = app_remove_special_characters($filename);
        $this->template_filepath = '';
    }
    
    function set_template_filepath($filepath)
    {
        if(is_file($filepath))
        {
            $this->template_filepath = $filepath;
        }
        else
        {
            die(TEXT_FILE_NOT_FOUD . ' ' . $filepath);
        }
    }
    
    function xlsx_from_array($export_data)
    {
        global $app_user;
        
       
        
        //load file from template
        if(strlen($this->template_filepath))
        {
            $reader = PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($this->template_filepath);
            $spreadsheet->setActiveSheetIndex(0);
        }
        else
        {        
            //create Spreadsheet
            $spreadsheet = new Spreadsheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator($app_user['name']);
            $spreadsheet->getActiveSheet()->setTitle(TEXT_LIST);    
        }
        
        
        // Set custom value binder to disable formulas    
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new items_export_value_binder() );
                        
        
        // Add some data
        $spreadsheet->getActiveSheet()->fromArray($export_data, null, 'A1');
        
        //autosize columns
        $highest_column = $spreadsheet->getActiveSheet()->getHighestColumn();
        
        for ($col = 'A'; $col != $highest_column; $col++)
        {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
            $spreadsheet->getActiveSheet()->getStyle($col.'1')->getFont()->setBold(true);
        }
        
        $spreadsheet->getActiveSheet()->getColumnDimension($highest_column)->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getStyle($highest_column.'1')->getFont()->setBold(true);
        
        // Rename worksheet
        
        
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . addslashes($this->filename) . '.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0                
        
        \PhpOffice\PhpSpreadsheet\Shared\File::setUseUploadTempDirectory(true);
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save('php://output');       
    }
}

class items_export_value_binder extends PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder
{
   public function bindValue(PhpOffice\PhpSpreadsheet\Cell\Cell $cell, mixed $value): bool
   {       
       // sanitize UTF-8 strings
        if (is_string($value)) {
            $value = PhpOffice\PhpSpreadsheet\Shared\StringHelper::sanitizeUTF8($value);
        } elseif ($value === null || is_scalar($value) || $value instanceof RichText) {
            // No need to do anything
        } elseif ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif ($value instanceof Stringable) {
            $value = (string) $value;
        } else {
            throw new SpreadsheetException('Unable to bind unstringable ' . gettype($value));
        }

        // if formula set it as string
        if (is_string($value) && strlen($value) > 1 && $value[0] === '=') 
        {
            $cell->setValueExplicit($value, PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        else
        {
            $cell->setValueExplicit($value, static::dataTypeForValue($value));
        }

        // Done!
        return true;
   }
}