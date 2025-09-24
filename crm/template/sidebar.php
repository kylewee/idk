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

<div class="page-sidebar-wrapper noprint">
    <div class="page-sidebar-wrapper">
        <div class="page-sidebar main-navbar-collapse collapse">
            <!-- BEGIN SIDEBAR MENU -->
            <ul class="page-sidebar-menu">
                <li class="sidebar-toggler-wrapper">

                    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->					
                    <div class="sidebar-toggler"></div>
                    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
                    
                    <?php
                    if (is_file(DIR_FS_UPLOADS . '/' . CFG_APP_LOGO))
                    {
                        if (is_image(DIR_FS_UPLOADS . '/' . CFG_APP_LOGO))
                        {

                            $html = '<img src="uploads/' . CFG_APP_LOGO . '" border="0" title="' . CFG_APP_NAME . '">';

                            if (strlen(CFG_APP_LOGO_URL) > 0)
                            {
                                $html = '<div class="logo"><a href="' . CFG_APP_LOGO_URL . '" target="_new">' . $html . '</a></div>';
                            }
                            else
                            {
                                $html = '<div class="logo"><a href="' . url_for('dashboard/') . '">' . $html . '</a></div>';
                            }

                            echo $html;
                        }
                    }
                    ?>          

                    <div class="clearfix"></div>


                </li>
                <li>

                    <?php
                    if (is_ext_installed())
                    {
                        echo global_search::render('search-form-sidebar');
                    }
                    ?>
                </li>

                <?php
                 //include php file from plugin                
                $is_plugin_sidebar = false;
                if(defined('AVAILABLE_PLUGINS'))
                {
                    foreach(explode(',', AVAILABLE_PLUGINS) as $plugin)
                    {
                        if(is_file($v = 'plugins/' . $plugin . '/sidebar.php'))
                        {
                            $is_plugin_sidebar = true;
                            require($v);                            
                        }
                    }
                }
                
                if (!$is_plugin_sidebar )
                {
                    $sidebarMenu = build_main_menu();
                    //print_rr($sidebarMenu);
                    echo renderSidebarMenu($sidebarMenu);
                }
                ?>

            </ul>
            <!-- END SIDEBAR MENU -->
        </div>
    </div>
</div>

