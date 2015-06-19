<?php

class crumbs_EntityPlugin_Callback implements crumbs_EntityPlugin {

  /**
   * @var callable
   */
  protected $callback;

  /**
   * @param callable $callback
   */
  function __construct($callback) {
    $this->callback = $callback;
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
    return call_user_func($this->callback, $entity, $entity_type, $distinction_key);
  }

}
