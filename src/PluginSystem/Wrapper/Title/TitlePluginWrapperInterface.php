<?php

namespace Drupal\crumbs\PluginSystem\Wrapper\Title;

interface TitlePluginWrapperInterface {

  /**
   * @param string|null $bestCandidate
   * @param int|null $bestWeight
   * @param string $path
   * @param array $routerItem
   */
  function findBestTitle(&$bestCandidate, &$bestWeight, $path, array $routerItem);

  /**
   * @return int|false
   */
  function getBestWeight();
}
