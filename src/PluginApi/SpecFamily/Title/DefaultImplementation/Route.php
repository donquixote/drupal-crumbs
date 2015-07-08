<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Title\DefaultImplementation;

use Drupal\crumbs\PluginApi\SpecFamily\Title\RouteInterface;
use Drupal\crumbs\PluginSystem\Tree\TreeNode;

class Route extends BaseFamily implements RouteInterface {

  /**
   * @var string
   *   E.g. 'node/%'.
   */
  protected $route;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findTitleTreeNode
   * @param string $route
   */
  function __construct(TreeNode $findTitleTreeNode, $route) {
    title::__construct($findTitleTreeNode);
    $this->route = $route;
  }

  /**
   * @param string $key
   * @param string $titlePath
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  public function fixedTitlePath($key, $titlePath) {
    // @todo Automatic description.
    return $this->monoPlugin($key, new \crumbs_MonoPlugin_FixedTitlePath($titlePath));
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   *   A ".*" will be appended to form a wildcard key.
   * @param \crumbs_MultiPlugin|\crumbs_MultiPlugin_FindTitleInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   * @throws \Exception
   */
  function multiPlugin($key, \crumbs_MultiPlugin_FindTitleInterface $plugin) {
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
   * @param \crumbs_MonoPlugin|\crumbs_MonoPlugin_FindTitleInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   * @throws \Exception
   */
  function monoPlugin($key, \crumbs_MonoPlugin_FindTitleInterface $plugin) {
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
  function titleCallback($key, $callback) {
    $plugin = new \crumbs_MonoPlugin_TitlePathCallback($callback);
    return $this->getTreeNode()
      ->child($key, TRUE)
      ->setRouteMonoPlugin($this->route, $plugin)
      ->offset();
  }

}
