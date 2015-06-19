<?php

namespace Drupal\crumbs\PluginSystem\Callback;

class CallbackPluginBuilder implements CallbackPluginBuilderInterface {

  /**
   * @var string
   */
  private $module;

  /**
   * @param string $module
   */
  function __construct($module) {
    $this->module = $module;
  }

  /**
   * @param callable $callback
   * @param string $key
   *
   * @return \crumbs_MonoPlugin
   */
  public function wrapParentCallback($callback, $key) {
    return new \crumbs_MonoPlugin_ParentPathCallback($callback, $this->module, $key);
  }

  /**
   * @param callable $callback
   * @param string $key
   *
   * @return \crumbs_MonoPlugin_FindTitleInterface
   */
  public function wrapTitleCallback($callback, $key) {
    return new \crumbs_MonoPlugin_TitleCallback($callback, $this->module, $key);
  }

  /**
   * @param callable $callback
   * @param string $key
   *
   * @return \crumbs_EntityPlugin
   */
  public function wrapEntityParentCallback($callback, $key) {
    return new \crumbs_EntityPlugin_Callback($callback, $this->module, $key, 'findParent');
  }

  /**
   * @param callable $callback
   * @param string $key
   *
   * @return \crumbs_EntityPlugin
   */
  public function wrapEntityTitleCallback($callback, $key) {
    return new \crumbs_EntityPlugin_Callback($callback, $this->module, $key, 'findParent');
  }
}
