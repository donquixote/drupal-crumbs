<?php

namespace Drupal\crumbs\PluginApi\HookArgument;

class Helper {

  /**
   * @var string $module
   *   The module for the current hook implementation.
   */
  private $module;

  /**
   * This is typically called before each invocation of hook_crumbs_plugins(),
   * to let the object know about the module implementing the hook.
   * Modules can call this directly if they want to let other modules talk to
   * the API object.
   *
   * @param string $module
   *   The module name.
   */
  function __construc($module) {
    $this->module = $module;
  }

  /**
   * @return string
   *   The module name.
   */
  public function getModule() {
    return $this->module;
  }

  /**
   * @param string $key
   *
   * @return string
   */
  public function buildAbsoluteKey($key) {
    return isset($key)
      ? $this->module . '-' . $key
      : $this->module;
  }

  /**
   * @param string $pluginOrKey
   *
   * @return \crumbs_EntityPlugin
   */
  public function entityPluginFromKey($pluginOrKey) {
    if ($pluginOrKey instanceof \crumbs_EntityPlugin) {
      return $pluginOrKey;
    }
    if (!isset($pluginOrKey)) {
      $class = $this->module . '_CrumbsEntityPlugin';
    }
    elseif (is_string($pluginOrKey)) {
      $class = $this->module . '_CrumbsEntityPlugin_' . $pluginOrKey;
    }
    else {
      throw new \InvalidArgumentException("Cannot create a plugin from the argument provided.");
    }
    if (!class_exists($class)) {
      throw new \InvalidArgumentException("Cannot create a plugin from a non-existing class.");
    }
    $plugin = new $class();
    if (!$plugin instanceof \crumbs_EntityPlugin) {
      throw new \InvalidArgumentException("The plugin has the wrong type.");
    }
    return $plugin;
  }

  /**
   * @param string $key
   *
   * @return \crumbs_MonoPlugin
   */
  public function monoPluginFromKey($key) {
    if (!isset($key)) {
      $class = $this->module . '_CrumbsMonoPlugin';
    }
    elseif (is_string($key)) {
      $class = $this->module . '_CrumbsMonoPlugin_' . $key;
    }
    else {
      throw new \InvalidArgumentException("Cannot create a plugin from the argument provided.");
    }
    if (!class_exists($class)) {
      throw new \InvalidArgumentException("Plugin class '$class' does not exist.");
    }
    $plugin = new $class();
    if (!$plugin instanceof \crumbs_MonoPlugin) {
      throw new \InvalidArgumentException("Plugin class '$class' does not implement crumbs_MonoPlugin.");
    }
    return $plugin;
  }

  /**
   * @param string $key
   *
   * @return \crumbs_MultiPlugin
   */
  public function multiPluginFromKey($key) {
    if (!isset($key)) {
      $class = $this->module . '_CrumbsMultiPlugin';
    }
    elseif (is_string($key)) {
      $class = $this->module . '_CrumbsMultiPlugin_' . $key;
    }
    else {
      throw new \InvalidArgumentException("Cannot create a plugin from the argument provided.");
    }
    if (!class_exists($class)) {
      throw new \InvalidArgumentException("Plugin class '$class' does not exist.");
    }
    $plugin = new $class();
    if (!$plugin instanceof \crumbs_MultiPlugin) {
      throw new \InvalidArgumentException("Plugin class '$class' does not implement crumbs_MultiPlugin.");
    }
    return $plugin;
  }

}
