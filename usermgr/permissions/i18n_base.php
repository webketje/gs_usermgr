<?php
if (myself(false) === 'load.php' && @$_GET['id'] === 'i18n_base') {
    add_action('page-access', 'i18n_base_pages_access');
}

add_filter('permissions-css', 'i18n_base_permissions_css');

function i18n_base_pages_access() {
    $usermgr = usermgr();
    $current_user = current_user();
    if ($current_user->cannot('access_pages')) {
        $usermgr->restrict_access();
    }
}

function i18n_base_permissions_css($style) {
    $usermgr = usermgr();
    $current_user = current_user();
    return pages_page_access($current_user, $style) . ($current_user->cannot('access_pages') ? '#nav_pages { display: none; }' : '');
}