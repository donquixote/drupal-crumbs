<?php

class crumbs_EntityPlugin_Callback implements crumbs_EntityPlugin {

  /**
   * @var callable
   */
  protected $callback;

  /**
   * @var string
   */
  protected $module;

  /**
   * @var string
   */
  protected $key;

  /**
   * @param callable $callback
   * @param string $module
   * @param string $key
   */
  function __construct($callback, $module, $key) {
    $this->callback = $callback;
    $this->module = $module;
    $this->key = $key;
  }

  /**
   * @return array
   */
  function __sleep() {
    // Do not serialize the callback.
    return array('module', 'key');
  }

  /**
   * @inheritdoc
   */
  function describe($api, $entity_type, $keys) {
    foreach ($keys as $key => $title) {
      $api->addRule($key, $title);
    }
  }

  /**
   * @inheritdoc
   */
  function entityFindCandidate($entity, $entity_type, $distinction_key) {
    if (!isset($this->callback)) {
      // Restore the callback after serialization.
      $this->callback = crumbs('callbackRestoration')->getEntityParentCallback($this->module, $this->key);
    }
    if (!empty($this->callback)) {
      return call_user_func($this->callback, $entity, $entity_type, $distinction_key);
    }
  }
}