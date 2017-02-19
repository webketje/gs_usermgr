<?php

/**
 * Plugin Name : UserMgr: interface
 * Description : A modern GS PHP API for managing user data & restricting user access.
 * Version     : 0.1
 * Release date: 2017-02-12
 * Author      : Kevin Van Lierde
 * Author URI  : http://webketje.com
 * License     : The MIT License (MIT)
 *
 * Copyright (c) 2017  Kevin Van Lierde <kevin.van.lierde@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 **/

// lang
i18n_merge('usermgr') || i18n_merge('usermgr', 'en_US');

// register plugin
register_plugin(
    'usermgr',
    i18n_r('usermgr/plugin_name'),
    '0.1',
    'Kevin Van Lierde',
    'https://github.com/webketje/gs_usermgr',
    i18n_r('usermgr/plugin_desc'),
    'settings',
    null
);

// only execute this plugin on the admin backend
if (!cookie_check()) {
    return;
}

define('GSUSERGROUPSPATH', GSPLUGINPATH . 'usermgr/groups/');

function usermgr($config = array())
{
    require_once 'usermgr/usermgr.list.php';
    require_once 'usermgr/usermgr.user.php';
    require_once 'usermgr/usermgr.group.php';
    require_once 'usermgr/usermgr.manager.php';
    return UserManager::create($config);
}

function current_user()
{
    global $USR, $config;
    $usermgr = usermgr($config);
    return $usermgr->get('users', $USR);
}

function usermgr_init()
{
    global $USR, $live_plugins, $SITEURL, $GSADMIN;

    // adapt to customize parts of this plugin
    $conf = array(
        'permissions' => 'ListMgr',
        'users' => 'ListMgr',
        'groups' => 'ListMgr',
        'siteurl' => $SITEURL,
        'admin' => $GSADMIN,
    );

    $usermgr = usermgr($conf);

    // bind $usermgr context to every user, in order for $user->can/cannot to work
    $usermgr->users->on('register', function ($user, $context) {
        $user->init($context);
    });

    // register standard permissions
    $standard_permissions = array(
    
        'access_pages', // pages.php
        'access_menu-manager', // menu-manager.php
        'create_page', // edit.php
        'edit_page', // edit.php?id
        'edit_page_options', // CSS
        'delete_page', // deletefile.php?id
    
        'access_files', // upload.php
        'upload_file', // upload.php !empty($_FILES)
        'delete_file', // deletefile.php?file
        'access_folders', // upload.php?path
        'create_folder', // upload.php?newfolder
        'delete_folder', // deletefile.php?folder
    
        'access_theme', // theme.php
        'access_theme-edit', // theme-edit.php
        'access_components', // components.php
        'create_component', // CSS
        'delete_component', // CSS
        'access_sitemap', // sitemap.php
    
        'access_backups', // backups.php
        'delete_all_backups', // backups.php?deleteall
        'delete_backup', // backup-edit.php?p=delete
        'restore_backup', // backup-edit.php?p=restore
        'access_archives', // archive.php
        'create_archive', // archive.php?do
        'delete_archive', // deletefile.php?zip
    
        'access_plugins', // plugins.php
        'toggle_plugin', // plugins.php?set
        'deactivate_plugin', // plugins.php?set + $live_plugins check
        'download_plugins', // plugins.php 'Download plugins' sidebar item
    
        'access_support', // support.php
        'access_health-check', // health-check.php
        'access_settings', // settings.php
        'access_profile', // GS 3.4-: CSS, GS 3.4+: profile.php
    
        'access_plugin', // load.php?id
    );

    foreach ($standard_permissions as $perm) {
        $usermgr->permissions->register($perm);
    }

    // allow other plugins to define their own permissions & user groups too
    exec_action('permissions-hook');

    // register std. usergroups (guest, publisher, developer, manager)
    usermgr_groups($usermgr->get('permissions'));

    // automatically grant all permissions to admin
    $usermgr->permissions->on('register', function ($perm, $context) {
        $admin = $context->get('groups', 'admin');
        $admin->grant($perm);
    });

    // register default (current) user
    $usermgr->register('users', User::create(User::fetch($USR)));

    // if the user XML has no <GROUP> node and a group file with the user's name exists, make him part of his 'single-member-group'
    // if the user XML has no <GROUP> node and a group file with the user's name doesn't exist, the user will be 'admin'
    // so as to make sure not to lock him out.
    $user = $usermgr->get('users', $USR);
    if (!$user->get('group') || !$usermgr->groups->exists($user->get('group'))) {
        if ($usermgr->groups->exists($user->get('usr')))
            $user->set('group', $user->get('usr'));
        else
            $user->set('group', 'admin');
    }

    // first entry of the slugmap array values is the 'parent' permission
    // the first 3 permissions cannot be denied
    $slugMap = array(
        'logout' => array('access_logout'),
        'unauthorized' => array('access_unauthorized'),
        'index' => array('access_login'),
        'pages' => array('access_pages'),
        'menu-manager' => array('access_menu-manager'),
        'edit' => array('create_page', 'edit_page', 'edit_page_options'),
        'deletefile' => array('access_deletefile', 'delete_page', 'delete_file', 'delete_folder', 'delete_archive'),
        'upload' => array('access_files', 'upload_file', 'create_folder'),
        'theme' => array('access_theme'),
        'theme-edit' => array('access_theme-edit'),
        'components' => array('access_components'),
        'sitemap' => array('access_sitemap'),
        'backups' => array('access_backups', 'delete_all_backups'),
        'backup-edit' => array('access_backup', 'delete_backup', 'restore_backup'),
        'archive' => array('access_archives', 'create_archive'),
        'plugins' => array('access_plugins', 'toggle_plugin', 'deactivate_plugin'),
        'support' => array('access_support'),
        'health-check' => array('access_health-check'),
        'settings' => array('access_settings'),
        'profile' => array('access_profile'),
        'load' => array('access_plugin'),
    );

    $page = basename(myself(false), '.php');
    $primary_permission = @$slugMap[$page][0];
    
    // block direct page access but in no case for logging out or for admin users
    if (!in_array($page, array('index', 'logout', 'unauthorized')) && !$user->is('admin')) {

        // required for accordingly updating the top-level navigation front-end on the unauthorized.php page
        gs_setcookie('GS_LAST_TAB', $page === 'load' ? $page === 'load' : $page);
        
        // if basic page access is restricted, look no further
        if ($user->cannot($primary_permission)) {
            $usermgr->restrict_access();
        }

        switch ($primary_permission) {
            case 'access_pages':
                // redirects the user to profile if pages access is restricted
                if ($user->cannot('access_pages')) {
                    $usermgr->groups->get($user->get('group'))->grant('access_profile');
                    redirect($SITEURL . $GSADMIN . (file_exists(GSADMINPATH . 'profile.php') ? '/profile.php' : '/settings.php'));
                }
                break;
            case 'create_page':
                if (isset($_GET['id']) && $user->cannot('edit_page')) {
                    $usermgr->restrict_access();
                }

                break;
            case 'access_files':
                if ((isset($_FILES['file']) && $user->cannot('upload_file')) ||
                    (isset($_GET['path']) && $user->cannot('access_folders')) ||
                    (isset($_GET['newfolder']) && $user->cannot('create_folder'))) {
                    $usermgr->restrict_access();
                }

                break;
            case 'access_backups':
                if (isset($_GET['deleteall']) && $user->cannot('delete_all_backups')) {
                    $usermgr->restrict_access();
                }

                break;
            case 'access_backup':
                if ((isset($_GET['p']) && $_GET['p'] === 'restore' && $user->cannot('restore_backup')) ||
                    (isset($_GET['p']) && $_GET['p'] === 'delete' && $user->cannot('delete_backup'))) {
                    $usermgr->restrict_access();
                }

                break;
            case 'access_archives':
                if (isset($_GET['do']) && $user->cannot('create_archive')) {
                    $usermgr->restrict_access();
                }

            case 'access_plugins':
                if ((isset($_GET['set']) && $user->cannot('toggle_plugin')) ||
                    (isset($_GET['set']) && $user->cannot('deactivate_plugin') && $live_plugins[$_GET['set']] === 'true')) {
                    $usermgr->restrict_access();
                }

                break;
            case 'access_deletefile':
                if ((isset($_GET['zip']) && $user->cannot('delete_archive')) ||
                    (isset($_GET['file']) && $user->cannot('delete_file')) ||
                    (isset($_GET['id']) && $user->cannot('delete_page')) ||
                    (isset($_GET['folder']) && $user->cannot('delete_folder'))) {
                    $usermgr->restrict_access();
                }

                break;
            case 'access_plugin':
                if (isset($_GET['id']) && $user->cannot('access_plugin_' . $_GET['id'])) {
                    $usermgr->restrict_access();
                }

                break;
            case 'access_settings':
                if ($user->can('access_profile')) {
                    if (file_exists(GSADMINPATH . 'profile.php'))
                        redirect($SITEURL . $GSADMIN . '/profile.php');
                }
                break;
        }

        // define your own rules for restricting page access through this hook
        exec_action('page-access');
    }
}

function usermgr_groups($standard_permissions)
{
    $usermgr = usermgr();
    $group_files = glob(GSPLUGINPATH . 'usermgr/groups/*.xml');

    $rawData = array_map(function ($entry) {
        $group_name = basename($entry, '.xml');
        return UserGroup::fetch($group_name);
    }, $group_files);

    // first sort user groups so that extend bases are added first,
    // and subsequent groups can successfully extend. Add admin group as base.
    $sortedNames = array('admin');
    $sortedGroups = array(array(
        'name' => 'admin',
        'grant' => $standard_permissions,
    ));

    foreach ($rawData as $group) {
        if (array_key_exists('extend', $group) && array_search($group['extend'], $sortedNames) === false) {
            array_unshift($sortedGroups, $group);
            array_unshift($sortedNames, $group['name']);
        } else {
            array_push($sortedGroups, $group);
            array_push($sortedNames, $group['name']);
        }
    }
    // important to pass $groupData by reference here!
    foreach ($sortedGroups as &$groupData) {
        $extendbase = array_search(@$groupData['extend'], $sortedNames);
        if (array_key_exists('extend', $groupData) && $extendbase > -1) {
            $base = UserGroup::create(array(
                'name' => $groupData['name'], 
                'grant' => $usermgr->get('groups', $sortedGroups[$extendbase]['name'])->permissions()
            ));
            if (array_key_exists('grant', $groupData))
                $base->grant($groupData['grant']);
            if (array_key_exists('deny', $groupData))
                $base->deny($groupData['deny']);
            $usermgr->register('groups', $base);
        } else
            $usermgr->register('groups', UserGroup::create($groupData));
    }
}

function usermgr_std_permissions()
{
    $usermgr = usermgr();
    $usermgr->register('permissions', array(

    ));
}

function usermgr_plugin_permissions($live_plugins)
{
    $usermgr = usermgr();
    $dirpath = GSPLUGINPATH . 'usermgr/permissions/';

    foreach ($live_plugins as $plugin => $activated) {
        if ($activated === 'true' && file_exists($dirpath . basename($plugin)))
            $perms = include_once $dirpath . basename($plugin);
    }
}

// don't allow non-admin users to activate/deactivate this plugin
// hooks @filter:permissions-css
function usermgr_deny_access_css($css)
{
    $current_user = current_user();
    if (!$current_user->is('admin')) {
        $css .= '#tr-usermgr { display: none; } #tr-unauthorized { display: none; }';
    }

    return $css;
}

// sets a meaningful ID for the plugins.php plugin entries
// so specific plugins can be hidden if permissions don't allow toggling them
// hooks @hook:plugin-hook
function usermgr_set_plugin_ids()
{
    global $table;
    $pluginfiles = glob(GSPLUGINPATH . '*.php');
    natcasesort($pluginfiles);
    $pluginfiles = array_values($pluginfiles);
    for ($i = 0; $i < count($pluginfiles); $i++) {
        $table = str_replace('id="tr-' . $i . '"', 'id="tr-' . lowercase(basename($pluginfiles[$i], '.php')) . '"', $table);
    }
    return $table;
}

function usermgr_unlink_pages($script)
{
    $user = current_user();
    if ($user->cannot('edit_page')) { 
       $script .= ' (function() {
          var pglinks = document.querySelectorAll(\'.pagetitle a\'), node;
          for (var i = 0, l = pglinks.length; i < l; i++) {
            node = document.createTextNode(pglinks[i].textContent);
            pglinks[i].parentNode.replaceChild(node, pglinks[i]);
          }
          }());
       ';
    }
    return $script;
}

global $live_plugins;

add_action('common', 'usermgr_init');
add_action('plugin-hook', 'usermgr_set_plugin_ids');
add_action('permissions-hook', 'usermgr_plugin_permissions', array($live_plugins));

// usermgr.stdaccess.php execs the hooks permissions-css & -js
require_once 'usermgr/usermgr.stdaccess.php';

add_action('header', 'usermgr_permissions_css');
add_action('footer', 'usermgr_permissions_js');

add_filter('permissions-css', 'usermgr_deny_access_css');
add_filter('permissions-js' , 'usermgr_unlink_pages');