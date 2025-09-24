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


require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox/Auth.php');
require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox/Files.php');
require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox/FileProperties.php');
require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox/FileRequests.php');
require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox/Misc.php');
require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox/Paper.php');
require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox/Sharing.php');
require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox/Users.php');
require('plugins/ext/file_storage_modules/dropbox/lib/sdk/Dropbox.php');

class dropbox
{

    public $title;
    public $site;
    public $api;
    public $version;

    function __construct()
    {
        $this->title = TEXT_MODULE_DROPBOX_TITLE;
        $this->site = 'https://www.dropbox.com';
        $this->api = 'https://github.com/lukeb2014/Dropbox-v2-PHP-SDK';
        $this->version = '1.0';
    }

    public function configuration()
    {
        $cfg = array();


        $cfg[] = array(
            'key' => 'app_key',
            'type' => 'input',
            'default' => '',
            'description' => '',
            'title' => 'App key',
            'params' => array('class' => 'form-control input-large required'),
        );
        
        $cfg[] = array(
            'key' => 'app_secret',
            'type' => 'input',
            'default' => '',
            'description' => TEXT_MODULE_DROPBOX_ACCESS_TOKEN_INFO,
            'title' => 'App secret',
            'params' => array('class' => 'form-control input-large required'),
        );
        
        $html = '
            <a href="https://www.dropbox.com/oauth2/authorize?token_access_type=offline&response_type=code&client_id=" onClick="return dropbox_get_app_code(this)" target="_blank" class="btn btn-default">Get App Code</a>
            <script>
                function dropbox_get_app_code(link)
                {
                    let app_key = $("#cfg_app_key").val()
                    
                    if(app_key.length==0)
                    {
                        alert("Please enter app key!")
                        return false
                    }
                    else
                    {
                        link.href = link.href+$("#cfg_app_key").val()
                        return true
                    }
                }           
            </script>            
            ';
        
        $cfg[] = array(
            'key' => 'app_code',
            'type' => 'input',
            'default' => '',
            'description' => $html,
            'title' => 'App code',
            'params' => array('class' => 'form-control input-xlarge required'),
        );
        
        $html = '<a href="javascript: dropbox_get_refresh_token()" class="btn btn-default">Generate Refresh Token</a>
            <script>
                function dropbox_get_refresh_token()
                {                    
                    let app_code = $("#cfg_app_code").val()
                    let app_key = $("#cfg_app_key").val()
                    let app_secret = $("#cfg_app_secret").val()

                    $.ajax({
                        url: url_for("ext/modules/dropbox","action=get_refresh_token"),
                        type: "POST",
                        data: {
                            app_code: app_code,
                            app_key: app_key,
                            app_secret: app_secret
                        }
                    }).done(function(msg){
                        data = JSON.parse(msg)
                        
                        if ("error" in data)
                        {
                          alert(JSON.stringify(data))
                          $("#cfg_refresh_token").val("")
                        }
                        else
                        {
                            $("#cfg_refresh_token").val(data.refresh_token)
                        }
                    })
                    
                    return false
                }
            </script>
            ';
        
        $cfg[] = array(
            'key' => 'refresh_token',
            'type' => 'input',
            'default' => '',
            'description' => $html,
            'title' => 'Refresh Token',
            'params' => array('class' => 'form-control input-xlarge required','readonly'=>'readonly'),
        );


        return $cfg;
    }

    function folder_prepare($folder)
    {
        $folder = explode('/', $folder);
        return '/' . $folder[0] . '-' . $folder[1] . '/';
    }

    function upload($module_id, $queue_info)
    {
        $cfg = modules::get_configuration($this->configuration(), $module_id);

        $file = attachments::parse_filename($queue_info['filename']);       

        if(!is_file($file['file_path']))
        {
            file_storage::remove_from_queue($queue_info['id']);
            return false;
        }

        //prepare folder Y-m
        $folder = $this->folder_prepare($file['folder']);


        $dropbox = new Dropbox\Dropbox($cfg['refresh_token'], $cfg['app_key'],$cfg['app_secret']);

        $resutl = $dropbox->files->upload($folder . $file['file'], $file['file_path'], "overwrite");
                

        if(!strstr($resutl, 'invalid_access_token'))
        {
            unlink($file['file_path']);

            file_storage::remove_from_queue($queue_info['id']);
        }
        else
        {
            file_storage::remove_from_queue($queue_info['id']);

            $error = $this->title . ': ' . 'upload error' . ($resutl ? ' (' . $resutl . ')' : '');

            modules::log_file_storage($error, $file);

            die($error);
        }
    }

    function download($module_id, $filename)
    {
        $cfg = modules::get_configuration($this->configuration(), $module_id);

        $file = attachments::parse_filename($filename);

        //prepare folder Y-m
        $folder = $this->folder_prepare($file['folder']);

        $dropbox = new Dropbox\Dropbox($cfg['refresh_token'], $cfg['app_key'],$cfg['app_secret']);

        $resutl_errors = $dropbox->files->download($folder . $file['file'], DIR_FS_TMP . $file['file']);



        if(is_file(DIR_FS_TMP . $file['file']) and!$resutl_errors)
        {
            file_storage::download_file_content($file['name'], DIR_FS_TMP . $file['file']);
        }
        else
        {
            print_rr(json_decode($resutl_errors, true));
            exit();
        }
    }

    function download_files($module_id, $files)
    {
        $cfg = modules::get_configuration($this->configuration(), $module_id);

        //download files to tmp folder

        $dropbox = new Dropbox\Dropbox($cfg['refresh_token'], $cfg['app_key'],$cfg['app_secret']);

        foreach(explode(',', $files) as $filename)
        {
            $file = attachments::parse_filename($filename);
            $folder = $this->folder_prepare($file['folder']);
            $path = $folder . $file['file'];

            $dropbox->files->download($path, DIR_FS_TMP . $file['file']);
        }

        //create zip archive
        $zip = new ZipArchive();
        $zip_filename = "attachments-" . time() . ".zip";
        $zip_filepath = DIR_FS_TMP . $zip_filename;
        $zip->open($zip_filepath, ZipArchive::CREATE);

        foreach(explode(',', $files) as $filename)
        {
            $file = attachments::parse_filename($filename);
            $zip->addFile(DIR_FS_TMP . $filename, $file['name']);
        }

        $zip->close();

        //check if zip archive created
        if(!is_file($zip_filepath))
        {
            exit("Error: cannot create zip archive in " . $zip_filepath);
        }

        //unlink downloaded files
        foreach(explode(',', $files) as $filename)
        {
            @unlink(DIR_FS_TMP . $filename);
        }

        //download archive
        file_storage::download_file_content($zip_filename, $zip_filepath);

        exit();
    }

    function delete($module_id, $files = array())
    {
        $cfg = modules::get_configuration($this->configuration(), $module_id);

        $dropbox = new Dropbox\Dropbox($cfg['refresh_token'], $cfg['app_key'],$cfg['app_secret']);

        foreach($files as $filename)
        {
            $file = attachments::parse_filename($filename);
            $folder = $this->folder_prepare($file['folder']);
            $path = $folder . $file['file'];

            $resutl = $dropbox->files->delete_v2($path);
        }
    }

}
