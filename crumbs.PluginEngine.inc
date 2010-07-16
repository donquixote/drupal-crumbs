<?php
// $Id$


class _crumbs_PluginEngine {
  
  protected $_order;
  protected $_subkey_order = array();
  protected $_objects;
  protected $_wildcard_objects = array();
  protected $_subkey_wildcard_objects = array();
  protected $_wildcard_subkey_order = array();
  
  
  function __construct(array $order, array $objects) {
    $this->_order = $order;
    $this->_objects = $objects;
    foreach ($this->_order as $order_key => $enabled) {
      list($object_key, $subkey) = explode(':', $order_key, 2);
      $this->_subkey_order[$object_key][$subkey] = $enabled;
    }
    foreach ($this->_objects as $object_key => $object) {
      if (isset($this->_order[$object_key])) {
        // do nothing
      }
      else if (isset($this->_order["$object_key:*"])) {
        $this->_subkey_wildcard_objects["$object_key:*"] = $object;
      }
      else {
        list($module, $instance_key) = explode('.', $object_key, 2);
        if ($this->_order["$module.*"]) {
          $this->_wildcard_objects["$module.*"][$instance_key] = $object;
        }
        else {
          $this->_wildcard_objects['*'][$object_key] = $object;
        }
      }
      if (method_exists($object, 'getRuleTitles')) {
        foreach ($object->getRuleTitles() as $subkey => $title) {
          $this->_wildcard_subkey_order[$object_key][$subkey] = TRUE;
        }
      }
    }
    if (!isset($this->_order['*'])) {
      $this->_order['*'] = TRUE;
    }
  }
  
  
  function invokeAll($invokeAction, $reverse = FALSE) {
    $order = $reverse ? array_reverse($this->_order) : $this->_order;
    foreach ($this->_order as $order_key => $enabled) {
      if ($enabled) {
        list($object_key, $subkey) = explode(':', $order_key, 2);
        list($module, $instance_key) = explode('.', $object_key, 2);
        
        if ($instance_key !== '*' && $object = $this->_objects[$object_key]) {
          $stop = $this->_invokeObject($invokeAction, $object, $object_key, $subkey);
          if ($stop === TRUE) {
            return;
          }
        }
        else if ($instance_key === '*' && !isset($subkey) && $objects = $this->_wildcard_objects[$object_key]) {
          foreach ($objects as $instance_key => $object) {
            $stop = $this->_invokeObject($invokeActon, $object, "$module.$instance_key", '*');
            if ($stop === TRUE) {
              return;
            }
          }
        }
        else if ($order_key === '*' && $objects = $this->_wildcard_objects['*']) {
          foreach ($objects as $object_key => $object) {
            $stop = $this->_invokeObject($invokeAction, $object, $object_key, '*');
            if ($stop === TRUE) {
              return;
            }
          }
        }
      }
    }
  }
  
  
  protected function _invokeObject($invokeAction, $object, $object_key, $subkey = '*') {
    $subkey_order = $this->_wildcard_subkey_order[$object_key];
    if ($subkey !== '*' || !is_array($subkey_order)) {
      $stop = $invokeAction->invoke($object, $object_key, $subkey);
      if ($stop === TRUE) {
        return TRUE;
      }
    }
    else {
      foreach ($subkey_order as $subkey => $enabled) {
        $stop = $invokeAction->invoke($object, $object_key, $subkey);
        if ($stop === TRUE) {
          return TRUE;
        }
      }
    }
  }
}


class _crumbs_InvokeAction_simple {
  
  protected $_method;
  protected $_args;
  
  function __construct($method, $args) {
    $this->_method = $method;
    foreach (array_keys($args) as $i => $k) {
      $this->_args[$i] = &$args[$k];
    }
  }
  
  function invoke($object, $object_key, $subkey) {
    $args = $this->_args;
    $args[] = $subkey;
    $method = $this->_method;
    if (method_exists($object, $method)) {
      call_user_func_array(array($object, $method), $args);
    }
  }
}



