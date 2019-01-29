<?php /* this file is part of the GS UserMgr plugin package, Copyright (c) 2017  Kevin Van Lierde <kevin.van.lierde@gmail.com> */

if (class_exists('UserManager')) {
  return;
}

class UserManager
{
  public static $instance;
  public $permissions;
  public $groups;
  public $users;

  public static function create($conf)
  {
    if (!self::$instance) {
      self::$instance = new self($conf);
    }

    return self::$instance;
  }

  public function __construct($conf = array())
  {
    $conf = is_object($conf) ? $conf : (object) $conf;

    if (property_exists($conf, 'permissions')) {
      $this->permissions = new $conf->permissions($this);
    }

    if (property_exists($conf, 'groups')) {
      $this->groups = new $conf->groups($this);
    }

    if (property_exists($conf, 'users')) {
      $this->users = new $conf->users($this);
    }

    $this->url = $conf->siteurl;
    $this->admin = $conf->admin;
  }

  public function get($list, $key = null)
  {
    if (property_exists($this, $list)) {
      if (isset($key)) {
        $entry = $this->$list->get($key);
        return $entry;
      }
      $list = $this->$list->get();
      return $list;
    }
  }

  public function register($list, $entry = null)
  {
    $params = func_get_args();
    array_shift($params);

    if (property_exists($this, $list) && isset($entry)) {
      call_user_func_array(array($this->$list, 'register'), $params);
    }
    return $this->get($list, $entry);
  }

  public function restrict_access()
  {
    // send out appropriate header
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Unauthorized');

    // avoid sending an entire 'unauthorized' admin page for AJAX requests
    if (requestIsAjax()) {
      die(i18n_r('usermgr/permission_denied_title'));
    }

    // if a custom 'unauthorized' page hasn't been set up in admin/, try to copy the plugin's default
    $dest = get_admin_path() . 'unauthorized.php';
    $source = GSPLUGINPATH . 'usermgr/unauthorized.php';

    if (!file_exists($dest)) {

      // if copy fails (e.g. because of directory permissions), fall back to blank page with default message
      if (!copy($source, $dest)) {
        echo ('<h1>' . i18n_r('usermgr/permission_denied_title') . '</h1>' . i18n_r('usermgr/permission_denied_content'));
        exit();
      } else {
        redirect($this->url . $this->admin . '/unauthorized.php');
      }

    } else {
      redirect($this->url . $this->admin . '/unauthorized.php');
    }
  }
}
