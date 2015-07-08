<?php

namespace Drupal\crumbs\PluginApi\Family\DefaultImplementation;

use Drupal\crumbs\PluginApi\Family\RouteInterface;
use Drupal\crumbs\PluginSystem\Tree\TreeNode;

class Route extends BasePluginFamily implements RouteInterface {

  /**
   * @var string
   *   E.g. 'node/%'.
   */
  protected $route;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findParentTreeNode
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findTitleTreeNode
   * @param string $route
   */
  function __construct(TreeNode $findParentTreeNode, TreeNode $findTitleTreeNode, $route) {
    parent::__construct($findParentTreeNode, $findTitleTreeNode);
    $this->route = $route;
  }

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function translateTitle($key, $title) {
    return $this->monoPlugin($key, new \crumbs_MonoPlugin_TranslateTitle($title));
  }

  /**
   * @param $key
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function skipItem($key) {
    // @todo Automatic description.
    return $this->monoPlugin($key, new \crumbs_MonoPlugin_SkipItem());
  }

  /**
   * @param string $key
   * @param string $parentPath
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  public function fixedParentPath($key, $parentPath) {
    // @todo Automatic description.
    return $this->monoPlugin($key, new \crumbs_MonoPlugin_FixedParentPath($parentPath));
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key, \crumbs_MultiPlugin $plugin) {
    return $this->pluginGetTreeNode($plugin)
      ->child($key, FALSE)
      ->setRouteMultiPlugin($this->route, $plugin)
      ->offset();
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function monoPlugin($key, \crumbs_MonoPlugin $plugin) {
    return $this->pluginGetTreeNode($plugin)
      ->child($key, TRUE)
      ->setRouteMonoPlugin($this->route, $plugin)
      ->offset();
  }

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function parentCallback($key, $callback) {
    $plugin = new \crumbs_MonoPlugin_ParentPathCallback($callback);
    return $this->getFindParentTreeNode()
      ->child($key, TRUE)
      ->setRouteMonoPlugin($this->route, $plugin)
      ->offset();
  }

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function titleCallback($key, $callback) {
    $plugin = new \crumbs_MonoPlugin_TitleCallback($callback);
    return $this->getFindTitleTreeNode()
      ->child($key, TRUE)
      ->setRouteMonoPlugin($this->route, $plugin)
      ->offset();
  }
}
