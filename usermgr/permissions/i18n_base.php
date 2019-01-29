<?php

function i18n_base_permissions_css($style)
{
    $usermgr      = usermgr();
    $current_user = current_user();
    return pages_page_access($style, $current_user) . ($current_user->cannot('access_pages') ? '#nav_pages { display: none; }' : '');
}

add_filter('permissions-css', 'i18n_base_permissions_css');

if (myself(false) === 'load.php' && @$_GET['id'] === 'i18n_base') {

    function i18n_base_pages_access()
    {
        $usermgr      = usermgr();
        $current_user = current_user();
        if ($current_user->cannot('access_pages')) {
            $usermgr->restrict_access();
        }
    }

    add_action('page-access', 'i18n_base_pages_access');
}
