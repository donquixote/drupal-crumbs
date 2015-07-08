<?php

namespace Drupal\crumbs\PluginSystem\Wrapper\Title;

class TrivialTitleMultiPluginWrapper implements TitlePluginWrapperInterface {

  /**
   * @var string
   */
  private $keyPrefix;

  /**
   * @var int
   */
  private $weight;

  /**
   * @var \crumbs_MultiPlugin_FindTitleInterface
   */
  private $multiPlugin;

  /**
   * @param string $keyPrefix
   * @param \crumbs_MultiPlugin_FindTitleInterface $plugin
   * @param int $weight
   */
  function __construct($keyPrefix, \crumbs_MultiPlugin_FindTitleInterface $plugin, $weight) {
    $this->keyPrefix = $keyPrefix;
    $this->multiPlugin = $plugin;
    $this->weight = $weight;
  }

  /**
   * @param string|null $bestCandidate
   * @param int|null $bestWeight
   * @param string $path
   * @param array $routerItem
   */
  function findBestTitle(&$bestCandidate, &$bestWeight, $path, array $routerItem) {
    if ($this->weight < $bestWeight) {
      $candidates = $this->multiPlugin->findTitle($path, $routerItem);
      if (is_array($candidates) && !empty($candidates)) {
        $bestCandidate = reset($candidates);
        $bestWeight = $this->weight;
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
