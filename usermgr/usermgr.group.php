<?php /* this file is part of the GS UserMgr plugin package, Copyright (c) 2017  Kevin Van Lierde <kevin.van.lierde@gmail.com> */

if (class_exists('UserGroup')) {
    return;
}

class UserGroup
{

    public $id;
    public $members      = array();
    private $permissions = array();

    // create a UserGroup instance from array parameters (fetch returnValue)
    public static function create($params)
    {
        if (is_string($params)) {
            $params = array('name' => $params);
        }

        return new self($params);
    }

    // fetch a user group record and return it as an associative array
    public static function fetch($groupname, $path = null)
    {
        $path     = (isset($path) ? $path : GSUSERGROUPSPATH) . $groupname . '.xml';
        $source   = getXML($path);
        $groupobj = array();
        if (is_object($source)) {
            $groupobj['name'] = $groupname;

            if (isset($source->extend)) {
                $groupobj['extend'] = (string) $source->extend;
            }

            if (count((array) $source->grant->permission)) {
                $groupobj['grant'] = (array) $source->grant->permission;
            }

            if (count((array) $source->deny->permission)) {
                $groupobj['deny'] = (array) $source->deny->permission;
            }

        }

        return $groupobj;
    }

    // store a UserGroup instance as xml (create returnValue)
    public static function store($groupobject)
    {
        $path   = GSUSERGROUPSPATH . $groupobject->name . '.xml';
        $xmlstr = '<?xml version="1.0" encoding="UTF-8"?><item></item>';
        $xml    = new SimpleXMLElement($xmlstr);

        foreach ($groupobject as $propkey => $propval) {
            $xml->addChild(uppercase($propkey), $propval);
        }

        if (!XMLsave($xml, $path)) {
            return i18n_r('CHMOD_ERROR');
        }

    }

    // return a UserGroup instance as JSON
    public static function toJSON($group)
    {
        if (is_array($group)) {
            return json_encode($group);
        }

    }

    private static function merge()
    {
        return array_values(array_unique(call_user_func_array('array_merge', func_get_args())));
    }

    public function __construct($params = array())
    {
        if (is_string($params)) {
            $this->id = $params;
            return;
        }

        $this->id = $params['name'];

        if (isset($params['grant'])) {
            $this->grant($params['grant']);
        }

        if (isset($params['deny'])) {
            $this->deny($params['deny']);
        }

    }

    // declare from which other user group this user group should inherit permissions, and eventually specify additional ones
    public function extend($user_group, $additional_permissions = array())
    {
        if (is_a($user_group, 'UserGroup')) {
            $this->permissions = self::merge($this->permissions, $user_group->permissions(), $additional_permissions);
        }

        return $this;
    }

    // declare which permissions this user group has
    public function grant($permissions = array())
    {
        if (is_string($permissions)) {
            $permissions = array($permissions);
        }

        $this->permissions = self::merge($this->permissions, $permissions);
        return $this;
    }

    // declare which permissions this user group has not
    public function deny($permissions = array())
    {
        if (is_string($permissions)) {
            $permissions = array($permissions);
        }

        $this->permissions = self::merge(array_diff($this->permissions, $permissions));
        return $this;
    }

    public function permissions()
    {
        return $this->permissions;
    }
}
