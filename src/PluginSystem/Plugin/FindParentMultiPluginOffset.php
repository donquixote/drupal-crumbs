<?php

namespace Drupal\crumbs\PluginSystem\Plugin;

use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;

class FindParentMultiPluginOffset implements \crumbs_MonoPlugin_FindParentInterface {

  /**
   * @var \crumbs_MultiPlugin_FindParentInterface
   */
  private $multiPlugin;

  /**
   * @var string
   */
  private $pluginKey;

  /**
   * @var \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap
   */
  private $localStatusMap;

  /**
   * @var int
   */
  private $weight;

  /**
   * @param \crumbs_MultiPlugin_FindParentInterface $multiPlugin
   * @param string $pluginKey
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $localStatusMap
   * @param int $weight
   */
  function __construct(
    \crumbs_MultiPlugin_FindParentInterface $multiPlugin,
    $pluginKey,
    PluginStatusWeightMap $localStatusMap,
    $weight
  ) {
    $this->multiPlugin = $multiPlugin;
    $this->pluginKey = $pluginKey;
    $this->localStatusMap = $localStatusMap;
    $this->weight = $weight;
  }

  /**
   * @param \crumbs_InjectedAPI_describeMonoPlugin $api
   *   Injected API object, with methods that allows the plugin to further
   *   describe itself.
   *
   * @return string|void
   *   As an alternative to the API object's methods, the plugin can simply
   *   return a string label.
   *
   * @throws \Exception
   */
  function describe($api) {
    throw new \Exception("Not supported.");
  }

  /**
   * Find candidates for the parent path.
   *
   * @param string $path
   *   The path that we want to find a parent for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string
   *   Parent path candidate.
   */
  function findParent($path, $item) {
    foreach ($this->multiPlugin->findParent($path, $item) as $candidateKey => $candidate) {
      if (!isset($candidate)) {
        continue;
      }
      if ($this->weight === $this->localStatusMap->keyGetWeightOrFalse($candidateKey)) {
        return $candidate;
      }
    }
    return NULL;
  }

  /**
   * @param string $path
   * @param array $item
   *
   * @return string[]
   */
  public function findAllParents($path, $item) {
    $all = array();
    foreach ($this->multiPlugin->findParent($path, $item) as $candidateKey => $candidate) {
      if (!isset($candidate)) {
        continue;
      }
      if ($this->weight === $this->localStatusMap->keyGetWeightOrFalse($candidateKey)) {
        $all[$this->pluginKey . '.' . $candidateKey] = $candidate;
      }
    }
    return $all;
  }

}
