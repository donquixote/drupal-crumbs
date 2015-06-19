<?php

namespace Drupal\crumbs\PluginApi\Collector\Dummy;

use Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface;
use Drupal\crumbs\PluginApi\PluginOffset\DummyOffset;
use Drupal\crumbs\PluginApi\PluginOffset\PluginOffset;

class DummyPluginCollector implements PluginCollectorInterface {

  /**
   * @param string $key
   * @param string $description
   */
  function addDescription($key, $description) {
    // Ignore.
  }

  /**
   * @param string $key
   * @param string $description
   *   The description in English.
   * @param string[] $args
   *   Placeholders to be inserted into the translated description.
   *
   * @see t()
   * @see format_string()
   */
  function translateDescription($key, $description, $args = array()) {
    // Ignore.
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   *   A ".*" will be appended to form a wildcard key.
   * @param \crumbs_MultiPlugin $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key, \crumbs_MultiPlugin $plugin) {
    return new DummyOffset();
  }

  /**
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   * @param \crumbs_MonoPlugin $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function monoPlugin($key, \crumbs_MonoPlugin $plugin) {
    return new DummyOffset();
  }

  /**
   * @param string $key
   * @param bool $status
   */
  function setDefaultStatus($key, $status) {
    // Ignore.
  }

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function pluginOffset($key) {
    return new DummyOffset();
  }
}
