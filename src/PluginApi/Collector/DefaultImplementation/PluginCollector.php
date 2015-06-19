<?php

namespace Drupal\crumbs\PluginApi\Collector\DefaultImplementation;

use Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollectionInterface;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollectionInterface;
use Drupal\crumbs\PluginApi\DescribeArgument\DescribeMultiPluginArg;
use Drupal\crumbs\PluginApi\PluginOffset\PluginOffset;

class PluginCollector implements PluginCollectorInterface {

  /**
   * @var PluginCollectionInterface
   */
  protected $pluginCollection;

  /**
   * @var \Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollectionInterface
   */
  protected $treeCollection;

  /**
   * @param \Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollectionInterface $pluginCollection
   * @param \Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollectionInterface $treeCollection
   */
  function __construct(
    PluginCollectionInterface $pluginCollection,
    TreeCollectionInterface $treeCollection
  ) {
    $this->pluginCollection = $pluginCollection;
    $this->treeCollection = $treeCollection;
  }

  /**
   * @param string $key
   * @param string $description
   */
  public function addDescription($key, $description) {
    $this->treeCollection->addDescription($key, $description);
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
  public function translateDescription($key, $description, $args = array()) {
    $this->treeCollection->translateDescription($key, $description, $args);
  }

  /**
   * @param string $key
   * @param bool $status
   */
  function setDefaultStatus($key, $status) {
    $this->treeCollection->setDefaultStatus($key, $status);
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
    $this->pluginCollection->addMultiPlugin($key, $plugin);
    $arg = new DescribeMultiPluginArg($key, $this->treeCollection);
    $descriptionsOrNull = $plugin->describe($arg);
    if (is_array($descriptionsOrNull)) {
      foreach ($descriptionsOrNull as $key => $description) {
        $arg->addDescription($description, $key);
      }
    }
    return new PluginOffset($this->treeCollection, $key . '.*');
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
    $this->pluginCollection->addMonoPlugin($key, $plugin);
    return new PluginOffset($this->treeCollection, $key);
  }

  /**
   * @return PluginCollectionInterface
   */
  function getPluginCollection() {
    return $this->pluginCollection;
  }

  /**
   * @return TreeCollectionInterface
   */
  function getTreeCollection() {
    return $this->getTreeCollection();
  }

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function pluginOffset($key) {
    return new PluginOffset($this->treeCollection, $key);
  }
}
