<?php /* this file is part of the GS UserMgr plugin package, Copyright (c) 2017  Kevin Van Lierde <kevin.van.lierde@gmail.com> */
  
if (class_exists('ListMgr'))
  return;

interface ListMgrInterface {
    public function get($entryID = null);
    public function register($entry = null);
    public function exists($entryID = null);
    public function on($evt, $listener);
}

class ListMgr implements ListMgrInterface {
    private $entries     = array();
    private $entry_keys  = array();
    private $events      = array();
    private $aliases     = array();
    private $context     = null;

    public function __construct()
    {
      $args = func_get_args();
      $this->context = array_shift($args);
      call_user_func_array(array($this, 'register'), $args);
    }

    public function toJSON()
    {
      return json_encode($this->entries);
    }

    public function register($entry = null)
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            $str = is_string($arg);
            if (!$str) {
                array_push($this->entries, $arg);
                $arg = (object) $arg;
                $arg = $arg->id;
            }
            if (array_search($arg, $this->entry_keys) === false) {
              array_push($this->entry_keys, $arg);
              $this->event('register', array($str ? $arg : $this->entries[count($this->entries) - 1], $this->context));
            }
        }

        return $this;  
    }

    public function get($entry = null)
    {
        if (!isset($entry))
            return count($this->entries) ? $this->entries : $this->entry_keys;
        
        if (array_search($entry, $this->entry_keys) === false) {
            return false;
        }

        if (count($this->entries))
            $entry = &$this->entries[array_search($entry, $this->entry_keys)];
        else 
            $entry = &$this->entry_keys[array_search($entry, $this->entry_keys)];
        return $entry;
    }

    public function exists($entry = null)
    {
        return $this->get($entry) !== false;
    }

    public function on($evt, $listener)
    {
        if (!array_key_exists($evt, $this->events))
            $this->events[$evt] = array();
        array_push($this->events[$evt], $listener);
    }

    private function event($evt, $args = array())
    {
        if (!array_key_exists($evt, $this->events))
            $this->events[$evt] = array();
        $queue = $this->events[$evt];
        foreach ($queue as $listener)
            call_user_func_array($listener, $args);
    }

  }