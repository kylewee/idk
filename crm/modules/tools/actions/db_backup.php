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

switch($app_module_action)
{
    case 'download':
        $info_query = db_query("select * from app_backups where id='" . db_input($_GET['id']) . "'");
        if($info = db_fetch_array($info_query))
        {
            $filename = $info['filename'];
            
            $backup_dir = $info['is_auto'] ? DIR_FS_BACKUPS_AUTO : DIR_FS_BACKUPS;

            if(is_file($backup_dir . $filename))
            {
                header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
                header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
                header("Cache-Control: no-cache, must-revalidate");
                header("Pragma: no-cache");
                header("Content-Type: Application/octet-stream");
                header("Content-disposition: attachment; filename=" . $filename);

                readfile($backup_dir . $filename);

                exit();
            }
            else
            {
                $alerts->add(TEXT_FILE_NOT_FOUD, 'error');

                redirect_to('tools/db_backup');
            }
        }
        break;
    case 'delete':

        $info_query = db_query("select * from app_backups where id='" . db_input($_GET['id']) . "'");
        if($info = db_fetch_array($info_query))
        {
            $filename = $info['filename'];
            $backup_dir = $info['is_auto'] ? DIR_FS_BACKUPS_AUTO : DIR_FS_BACKUPS;

            if(is_file($backup_dir . $filename))
            {
                unlink($backup_dir . $filename);

                $alerts->add(TEXT_BACKUP_DELETED, 'success');
            }
            else
            {
                $alerts->add(TEXT_FILE_NOT_FOUD, 'error');
            }

            db_delete_row('app_backups', $info['id']);
            
            if($info['is_auto'])
            {
                redirect_to('tools/db_backup_auto');
            }
        }

        redirect_to('tools/db_backup');
        break;
    case 'backup':

        $backup = new backup();
        $backup->set_description($_POST['description']);
        $backup->create();

        $alerts->add(TEXT_BACKUP_CREATED, 'success');

        redirect_to('tools/db_backup');
        break;
    case 'export_template':

        $filename = mb_ereg_replace('_+', '_', mb_ereg_replace("[^[:alnum:]]", '_', CFG_APP_NAME));

        $filename = $filename . '_' . date('Y-m-d_H-i') . '_Rukovoditel_' . PROJECT_VERSION . '.sql';

        $backup = new backup();
        $backup->set_filename($filename);
        $backup->create();

        header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-Type: Application/octet-stream");
        header("Content-disposition: attachment; filename=" . $filename);

        readfile(DIR_FS_BACKUPS . $filename);

        unlink(DIR_FS_BACKUPS . $filename);

        exit();

        break;
}

backup::reset();
