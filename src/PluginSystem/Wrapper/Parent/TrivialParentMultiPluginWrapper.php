<?php

namespace Drupal\crumbs\PluginSystem\Wrapper\Parent;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;

class TrivialParentMultiPluginWrapper implements ParentPluginWrapperInterface {

  /**
   * @var string
   */
  private $keyPrefix;

  /**
   * @var int
   */
  private $weight;

  /**
   * @var \crumbs_MultiPlugin_FindParentInterface
   */
  private $multiPlugin;

  /**
   * @param string $keyPrefix
   * @param \crumbs_MultiPlugin_FindParentInterface $plugin
   * @param int $weight
   */
  function __construct($keyPrefix, \crumbs_MultiPlugin_FindParentInterface $plugin, $weight) {
    $this->keyPrefix = $keyPrefix;
    $this->multiPlugin = $plugin;
    $this->weight = $weight;
  }

  /**
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   * @param int|null $bestWeight
   * @param string $path
   * @param array $routerItem
   */
  function findBestParent(CheckerInterface $checker, &$bestWeight, $path, array $routerItem) {
    if ($this->weight < $bestWeight) {
      $candidates = $this->multiPlugin->findParent($path, $routerItem);
      if (is_array($candidates) && !empty($candidates)) {
        foreach ($candidates as $key => $candidate) {
          if ($checker->checkParentPath($candidate, $this->keyPrefix . $key)) {
            $bestWeight = $this->weight;
            break;
          }
        }
      }
    }
  }

  /**
   * @return int|false
   */
  function getBestWeight() {
    return $this->weight;
  }

}
