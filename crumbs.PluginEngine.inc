<?php
// $Id$


class _crumbs_PluginEngine {
  
  protected $_x;
  
  
  function __construct($x) {
    $this->_x = $x;
  }
  
  
  function invokeAll($invokeAction, $reverse = FALSE) {
    $x = $this->_x;
    $order = $reverse ? array_reverse($x->order) : $x->order;
    foreach ($order as $order_key => $enabled) {
      if ($enabled) {
        if (isset($x->rules[$order_key])) {
          $this->_invokeRule($invokeAction, $order_key);
        }
        else if (isset($x->wildcard_rules[$order_key])) {
          foreach ($x->wildcard_rules[$order_key] as $rule_key) {
            $this->_invokeRule($invokeAction, $rule_key);
          }
        }
      }
    }
  }
  
  protected function _invokeRule($invokeAction, $rule_key) {
    $x = $this->_x;
    list($object_key, $subkey) = explode(':', $rule_key, 2);
    $object = $x->objects[$object_key];
    if ($object) {
      return $invokeAction->invoke($object, $object_key, $subkey);
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




class _crumbs_PluginInstanceBasket {
  protected $_x;
  protected $_module;
  protected $_objects = array();
  protected $_order;
  
  function __construct($x) {
    $this->_x = $x;
  }
  
  function addPluginInstance($instance_key, $object, $title = NULL) {
    $x = $this->_x;
    $object_key = $x->module . '.' . $instance_key;
    if (isset($title)) {
      $x->titles[$object_key] = $title;
    }
    _crumbs_init_instance($x, $object_key, $object);
  }
  
  function setDefaultInstanceStatus($default_status) {
    $x = $this->_x;
    $module = $x->module;
    if (!isset($x->order["$module.*"])) {
      if ($default_status === TRUE || $default_status === FALSE) {
        $x->order["$module.*"] = $default_status;
      }
    }
  }
}




class _crumbs_PluginRuleBasket {
  protected $_x;
  
  function __construct($x) {
    $this->_x = $x;
  }
  
  function addRule($subkey, $title = NULL) {
    $x = $this->_x;
    $object_key = $x->object_key;
    $order_key = "$object_key:$subkey";
    $x->rules[$order_key] = $order_key;
    if (isset($title)) {
      $x->titles[$order_key] = $title;
    }
  }
  
  function setDefaultRuleStatus($default_status) {
    $x = $this->_x;
    $object_key = $x->object_key;
    if (!isset($x->order["$object_key:*"])) {
      if ($default_status === TRUE || $default_status === FALSE) {
        $x->order["$object_key.*"] = $default_status;
      }
    }
  }
}



function _crumbs_load_plugin_data() {
  $x = new stdClass;
  $x->order = _crumbs_get_settings() + array('*' => TRUE);
  _crumbs_init_modules($x);
  return $x;
}


function _crumbs_load_plugin_engine() {
  $x = _crumbs_load_plugin_data();
  return new _crumbs_PluginEngine($x);
}



function _crumbs_init_modules($x) {
  $x->rules = array();
  $x->wildcard_rules['*'] = array();
  foreach (module_list(FALSE, TRUE) as $module) {
    _crumbs_init_module($x, $module);
  }
  foreach ($x->rules as $order_key) {
    list($object_key, $subkey) = explode(':', $order_key, 2);
    list($module, $instance_key) = explode('.', $object_key, 2);
    
    if (isset($x->order[$order_key])) {
      // don't fall back to wildcard.
    }
    else if ($subkey && isset($x->order["$object_key:*"])) {
      $x->wildcard_rules["$object_key:*"][$order_key] = $order_key;
    }
    else if ($instance_key && isset($x->order["$module.*"])) {
      $x->wildcard_rules["$module.*"][$order_key] = $order_key;
    }
    else {
      $x->wildcard_rules["*"][$order_key] = $order_key;
    }
  }
}



function _crumbs_init_module($x, $module) {
  $x->module = $module;
  $class = $module . '_class_CrumbsParentFinder';
  if (class_exists($class)) {
    if (method_exists($class, 'initInstances')) {
      $x->wildcard_rules["$module.*"] = array();
      $instanceBasket = new _crumbs_PluginInstanceBasket($x);
      call_user_func(array($class, 'initInstances'), $instanceBasket);
    }
    else if (method_exists($class, 'construct')) {
      $x->wildcard_rules["$module.*"] = array();
      $instanceBasket = new _crumbs_PluginInstanceBasket($x);
      $objects = call_user_func(array($class, 'construct'));
      foreach ($objects as $instance_key => $object) {
        $instanceBasket->addPluginInstance($instance_key, $object);
      }
      if (method_exists($class, 'enabledByDefault_static')) {
        $default_state = call_user_func(array($class, 'enabledByDefault_static'));
        $instanceBasket->setDefaultInstanceStatus($default_state);
      }
    }
    else {
      _crumbs_init_instance($x, $module, new $class);
    }
  }
}


function _crumbs_init_instance($x, $object_key, $object) {
  $x->objects[$object_key] = $object;
  $x->object_key = $object_key;
  if (!$object_key) {
    // this should never happen..
  }
  else if (method_exists($object, 'initRules')) {
    $x->wildcard_rules["$object_key:*"] = array();
    $ruleBasket = new _crumbs_PluginRuleBasket($x);
    $object->initRules($ruleBasket);
  }
  else if (method_exists($object, 'getRuleTitles')) {
    $x->wildcard_rules["$object_key:*"] = array();
    $titles = $object->getRuleTitles();
    $ruleBasket = new _crumbs_PluginRuleBasket($x);
    foreach ($titles as $subkey => $title) {
      $ruleBasket->addRule($subkey, $title);
    }
    if (method_exists($object, 'enabledByDefault')) {
      $default_state = $object->enabledByDefault();
      $ruleBasket->setDefaultRuleStatus($default_state);
    }
  }
  else {
    $x->rules[$object_key] = $object_key;
    if (method_exists($object, 'getRuleTitle')) {
      $x->titles[$object_key] = $object->getRuleTitle();
    }
  }
}




