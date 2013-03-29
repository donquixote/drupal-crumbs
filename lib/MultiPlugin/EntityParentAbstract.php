<?php

abstract class crumbs_MultiPlugin_EntityParentAbstract implements crumbs_MultiPlugin {

  protected $plugin;

  /**
   * @param object $plugin
   *   The object that can actually determine a parent path for the entity.
   */
  function __construct($plugin) {
    $this->plugin = $plugin;
  }
}
