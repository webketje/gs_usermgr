<?php /* this file is part of the GS UserMgr plugin package, Copyright (c) 2017  Kevin Van Lierde <kevin.van.lierde@gmail.com> */

function pages_page_access($style, $current_user) {
    if ($current_user->cannot('create_page')) {
        $style .= '#sb_newpage { display: none; }';
    }

    if ($current_user->cannot('access_menu-manager')) {
        $style .= '#sb_menumanager { display: none; }';
    }

    if ($current_user->cannot('delete_page')) {
        $style .= '#editpages th:last-child, #editpages td.delete { display: none; }';
    }

    if ($current_user->cannot('edit_page')) {
        $style .= 'td.pagetitle a { display: none; }';
    }

    return $style;
}

function edit_page_access($style, $current_user) {
    if ($current_user->cannot('create_page')) {
        $style .= '#sb_newpage, #edit #dropdown li a[href*="action=clone"] { display: none; }';
    }

    if ($current_user->cannot('access_menu-manager')) {
        $style .= '#sb_menumanager, [href^="menu-manager"] { display: none; }';
    }

    if ($current_user->cannot('delete_page')) {
        $style .= '#dropdown li.alertme { display: none; }';
    }

    if (!empty($_GET['id']) && $current_user->cannot('edit_page_options')) {
        $style .= '#metadata_toggle { display: none; }';
    }

    return $style;
}

function menumanager_page_access($style, $current_user) {
    if ($current_user->cannot('edit_page')) {
        $style .= '#sb_newpage { display: none; }';
    }

    return $style;
}

function upload_page_access($style, $current_user) {
    if ($current_user->cannot('create_folder')) {
        $style .= '#new-folder { display: none; }';
    }

    if ($current_user->cannot('upload_file')) {
        $style .= '#sidebar .snav li.upload, #sb_filesize, .uploadform { display: none; }';
    }

    if ($current_user->cannot('delete_file')) {
        $style .= '#imageTable td.delete, #imageTable th:last-child { display: none; }';
    }

    return $style;
}

function theme_page_access($style, $current_user) {
    if ($current_user->cannot('access_theme-edit')) {
        $style .= '#sb_themeedit { display: none; }';
    }

    if ($current_user->cannot('access_components')) {
        $style .= '#sb_components { display: none; }';
    }

    if ($current_user->cannot('access_sitemap')) {
        $style .= '#sb_sitemap { display: none; }';
    }

    return $style;
}

function components_page_access($style, $current_user) {
    if ($current_user->cannot('access_theme-edit')) {
        $style .= '#sb_themeedit { display: none; }';
    }

    if ($current_user->cannot('access_sitemap')) {
        $style .= '#sb_sitemap { display: none; }';
    }

    if ($current_user->cannot('create_components')) {
        $style .= '.edit-nav #addcomponent { display: none; }';
    }

    if ($current_user->cannot('delete_components')) {
        $style .= '.comptable td.delete { display: none; }';
    }

    return $style;
}

function backups_page_access($style, $current_user) {
    if ($current_user->cannot('delete_all_backups')) {
        $style .= '.edit-nav  a.confirmation { display: none; }';
    }

    if ($current_user->cannot('delete_backup')) {
        $style .= '#editpages td.delete, #editpages th:last-child { display: none; }';
    }

    if ($current_user->cannot('access_archives')) {
        $style .= '#sb_archives { display: none; }';
    }

    return $style;
}

function archive_page_access($style, $current_user) {
    if ($current_user->cannot('delete_archive')) {
        $style .= '.delete a.delconfirm { display: none; }';
    }

    return $style;
}

function backupedit_page_access($style, $current_user) {
    if ($current_user->cannot('access_archives')) {
        $style .= '#sb_archives { display: none; }';
    }

    if ($current_user->cannot('delete_backup')) {
        $style .= '#delback { display: none; }';
    }

    if ($current_user->cannot('restore_backup')) {
        $style .= '.edit-nav a[accesskey="r"] { display: none; }';
    }

    return $style;
}

function plugins_page_access($style, $current_user) {
    if ($current_user->cannot('toggle_plugin')) {
        $style .= 'td.status, th:last-child { display: none; }';
    }

    if ($current_user->cannot('deactivate_plugin')) {
        $style .= '.delete a.delconfirm { display: none; } .enabled a.toggleEnable { display: none; }';
    }

    return $style;
}

function settings_page_access($style, $current_user) {
    if ($current_user->cannot('access_settings')) {
        $style .= '.main > .leftsec:nth-child(2), .main > .rightsec:nth-child(3), .main > h3:first-child, #sb_settings, ';
        $style .= '.main > .leftsec:nth-child(6), .main > p.inline:nth-child(5) { display: none; } #maincontent .main #profile { padding-top: 10px; }';
    }
    return $style;
}

function healthcheck_page_access($style, $current_user) {
    if ($current_user->cannot('access_support')) {
        $style .= '#sb_support { display: none; }';
    }
    return $style;
}

function usermgr_permissions_css() {
    $current_user = current_user();
    $current_page = basename(str_replace('-', '', myself(false)), '.php');
    $style = '';
    // top-level nav tabs
    $nav_tabs = array('pages', 'upload', 'backups', 'theme', 'plugins');
    foreach ($nav_tabs as $p) {
        if ($current_user->cannot('access_' . ($p === 'upload' ? 'files' : $p))) {
            $style .= '#nav_' . $p . ' { display: none; }';
        }

    }

    // right nav pills
    if ($current_user->cannot('access_health-check') && $current_user->cannot('access_support')) {
        $style .= '.nav li.rightnav:last-child { display: none; } .wrapper .nav li.rightnav a.settings { border: none; border-radius: 3px; }';
    }

    if ($current_user->can('access_support') && $current_user->cannot('access_health-check')) {
        $style .= '.nav li.rightnav .warning, #sb_healthcheck { display: none; }';
    }

    if ($current_user->cannot('access_settings') && $current_user->cannot('access_profile') && $current_user->can('access_pages')) {
        $style .= '.nav li.rightnav:nth-last-child(2) { display: none; }';
    }

    if ($current_user->cannot('access_settings') && $current_user->can('access_profile')) {
        $style .= '#sidebar .snav li#sb_settingsprofile a { -webkit-transition: none; -o-transition: none; -moz-transition: none; transition: none; }';
    }

    if (myself(false) !== 'load.php' && function_exists($current_page . '_page_access')) {
        $style = call_user_func_array(str_replace('-', '', $current_page) . '_page_access', array($style, $current_user));
    }

    $style = exec_filter('permissions-css', $style, $current_user);

    echo '<style>' . $style . ' </style>';
}

function usermgr_permissions_js() {
    $current_user = current_user();
    $current_page = myself(false);
    $script = '';

    if ('settings.php' === $current_page && $current_user->cannot('access_settings')) {
        $script .= 'document.querySelector(\'#sb_settingsprofile a\').className += \' current\';';
    }
    if ($current_user->can('access_support') && $current_user->cannot('access_health-check')) {
        $script .= 'document.querySelector(\'[href^="health-check.php"]\').href = \'support.php\';';
    }

    $script = exec_filter('permissions-js', $script);
    echo '<script type="text/javascript">' . $script . '</script>';
}
