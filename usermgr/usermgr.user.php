<?php /* this file is part of the GS UserMgr plugin package, Copyright (c) 2017  Kevin Van Lierde <kevin.van.lierde@gmail.com> */

if (class_exists('User'))
	return;
	
class User {
	
		private $pwd = NULL;
		private $email    = NULL;
		private $timezone = NULL;
		private $usr     = NULL;
		private $name     = NULL;
		private $group    = NULL;
		private $path     = NULL;
		private $lang     = NULL;
		private $htmledit = NULL;
		private $modified = false;
		public $id;
		protected $permcache = array();
	
        // create a User instance from array parameters (fetch returnValue)
		public static function create($username = null)
		{
			return new self($username);
		}
		
		// fetch a user record and return it as an associative array
		public static function fetch($username, $path = null)
		{
			$path   = (isset($path) ? $path : GSUSERSPATH) . $username . '.xml';
			$source  = getXML($path);
			$userobj = array();

			if (is_object($source))
				foreach ($source as $xmlnode) {
					$name = lowercase($xmlnode->getName());
					$userobj[$name] = (string) $xmlnode;
				}

			return $userobj;
		}

		// store a User instance as xml (create returnValue)
		public static function store($userobject, $path = null)
		{
			$path   = (isset($path) ? $path : GSUSERSPATH) . $userobject->usr . '.xml';
			$xmlstr = '<?xml version="1.0" encoding="UTF-8"?><item></item>';
			$xml    = new SimpleXMLElement($xmlstr);

			foreach ($userobject as $propkey => $propval) {
				$xml->addChild(uppercase($propkey), $propval);
			}
	
			if (!XMLsave($xml, $path))
				return i18n_r('CHMOD_ERROR');
		}

		// return a User instance as JSON
		public static function toJSON($user)
		{
			if (is_array($user))
				return json_encode($user);
		}
  
		public function __construct($params) 
		{			
			foreach ($params as $param => $value) {
				$this->$param = $value;
			}

			$this->id = $this->usr;
		}

		// bind a context with permissions and groups available to this User instance
		public function init($context)
		{
            $this->context = $context;
		}
		
		// generic getter
		public function get($prop) 
		{
			return $this->$prop;
		}
		
		// generic setter
		public function set($prop, $value)
		{
			if ($value !== $this->$prop && !$this->modified)
			    $this->modified = true;
			$this->$prop = $value;
		}
		
		public function is($state) 
		{
			if ($state === 'modified')
				return $this->modified;
			return $this->group === $state;
		}
		
		public function can($perm) 
		{
			if ($this->group === 'admin')
			    return true;
		    if (array_key_exists($perm, $this->permcache))
			    return $this->permcache[$perm];

		    $group = $this->context->get('groups', $this->group);
		    $result = array_search($perm, $group->permissions()) !== false || $this->context->get('permissions', $perm) === false;
			$this->permcache[$perm] = $result;
			return $result;
		}
		
		public function cannot($perm, $ctx = '') 
		{
		    if (array_key_exists('!' . $perm, $this->permcache))
			    return $this->permcache['!' . $perm];
		    $group = $this->context->get('groups', $this->group);
		    $result = !$this->can($perm);
			$this->permcache['!' . $perm] = $result;
			return $result;
		}
	
	}
