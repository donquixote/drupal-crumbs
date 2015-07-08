<?php

namespace Drupal\crumbs_ui\TreePosition;

use Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface;

interface ResultTreePositionInterface extends TreePositionInterface {

  /**
   * @return string|null
   */
  function getCandidate();
}
