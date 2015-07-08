<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Parent\DefaultImplementation;

use Drupal\crumbs\PluginApi\SpecFamily\Parent\RouteInterface;
use Drupal\crumbs\PluginSystem\Tree\TreeNode;

/**
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Title\DefaultImplementation\Route
 */
class Route extends BaseFamily implements RouteInterface {

  /**
   * @var string
   *   E.g. 'node/%'.
   */
  protected $route;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findParentTreeNode
   * @param string $route
   */
  function __construct(TreeNode $findParentTreeNode, $route) {
    parent::__construct($findParentTreeNode);
    $this->route = $route;
  }

  /**
   * @param string $key
   * @param string $parentPath
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */ /* PARENT ONLY */
  public function fixedParentPath($key, $parentPath) {
    // @todo Automatic description.
    return $this->monoPlugin($key, new \crumbs_MonoPlugin_FixedParentPath($parentPath));
  } /* */

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */ /* TITLE ONLY * /
  function translateTitle($key, $title) {
    return $this->monoPlugin($key, new \crumbs_MonoPlugin_TranslateTitle($title));
  } /* */

  /**
   * @param $key
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */ /* TITLE ONLY * /
  function skipItem($key) {
    // @todo Automatic description.
    return $this->monoPlugin($key, new \crumbs_MonoPlugin_SkipItem());
  } /* */

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   *   A ".*" will be appended to form a wildcard key.
   * @param \crumbs_MultiPlugin|\crumbs_MultiPlugin_FindParentInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   * @throws \Exception
   */
  function multiPlugin($key, \crumbs_MultiPlugin_FindParentInterface $plugin) {
    return $this->getTreeNode()
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
   * @param \crumbs_MonoPlugin|\crumbs_MonoPlugin_FindParentInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   * @throws \Exception
   */
  function monoPlugin($key, \crumbs_MonoPlugin_FindParentInterface $plugin) {
    return $this->getTreeNode()
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
    return $this->getTreeNode()
      ->child($key, TRUE)
      ->setRouteMonoPlugin($this->route, $plugin)
      ->offset();
  }

}
