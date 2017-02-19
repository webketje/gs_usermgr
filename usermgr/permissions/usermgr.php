<?php

$usermgr = usermgr();

// register standard permissions
$usermgr->permissions->register(

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

    'access_plugin' // load.php?id
);