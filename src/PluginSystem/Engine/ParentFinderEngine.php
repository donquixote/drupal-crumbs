<?php

namespace Drupal\crumbs\PluginSystem\Engine;

use Drupal\crumbs\PluginSystem\Plugin\FindParentMultiPluginOffset;
use Drupal\crumbs\Router\RouterInterface;

class ParentFinderEngine {

  /**
   * @var \crumbs_MonoPlugin_FindParentInterface[]
   *   Format: $[$pluginKey] = $plugin
   */
  private $pluginsSorted;

  /**
   * @var RouterInterface
   */
  protected $router;

  /**
   * @param \crumbs_MonoPlugin_FindParentInterface[] $pluginsSorted
   * @param \Drupal\crumbs\Router\RouterInterface $router
   */
  function __construct(array $pluginsSorted, RouterInterface $router) {
    $this->pluginsSorted = $pluginsSorted;
    $this->router = $router;
  }

  /**
   * @param string $path
   * @param array $item
   *
   * @return string|NULL
   *   The normalized parent path, or NULL.
   */
  function findParent($path, array $item) {
    foreach ($this->pluginsSorted as $key => $plugin) {
      $candidate = $plugin->findParent($path, $item);
      if (isset($candidate)) {
        $candidate = $this->processParentCandidate($candidate);
        if (isset($candidate)) {
          return $candidate;
        }
      }
    }
    return NULL;
  }

  /**
   * @param string $path
   * @param array $item
   *
   * @return string[]
   *   The normalized parent path candidates.
   */
  function findAllParents($path, array $item) {
    $all = array();
    foreach ($this->pluginsSorted as $key => $plugin) {
      if ($plugin instanceof FindParentMultiPluginOffset) {
        $all += $plugin->findAllParents($path, $item);
      }
      elseif ($plugin instanceof \crumbs_MonoPlugin_FindParentInterface) {
        $candidate = $plugin->findParent($path, $item);
        if (isset($candidate)) {
          $all[$key] = $candidate;
        }
      }
      else {
        throw new \RuntimeException('Invalid plugin type.');
      }
    }
    return $all;
  }

  /**
   * @param string $parent_raw
   *
   * @return string|NULL
   *   The normalized parent path, or NULL.
   */
  protected function processParentCandidate($parent_raw) {
    if ($this->router->urlIsExternal($parent_raw)) {
      // Always discard external paths.
      return NULL;
    }
    return $this->router->getNormalPath($parent_raw);
  }
}
