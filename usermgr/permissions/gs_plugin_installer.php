<?php

$usermgr = usermgr();
$usermgr->register('permissions', 'access_plugin_gs_plugin_installer'); 

function gs_plugin_installer_permissions_css($style) {
    $current_user = current_user();
    return $current_user->cannot('access_plugin_gs_plugin_installer') ? '#sb_gs_plugin_installer { display: none; }' : '';
}

add_filter('permissions-css', 'gs_plugin_installer_permissions_css');