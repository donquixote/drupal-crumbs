<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\PluginSystem\Tree\TreeUtil;

class TitlePluginDemo extends ParentPluginDemo {

  /**
   * @return array
   *   A render array.
   */
  function build() {
    return parent::build();
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  protected function getTree() {
    return crumbs()->qualifiedTitleTree;
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   * @param array $routerItem
   *
   * @return array|null|string
   */
  protected function pluginCollectResults(\crumbs_PluginInterface $plugin, array $routerItem) {

    $path = $routerItem['link_path'];
    if ($plugin instanceof \crumbs_MultiPlugin_FindTitleInterface) {
      $candidates = $plugin->findTitle($path, $routerItem);
      if (is_array($candidates) && !empty($candidates)) {
        return TreeUtil::spliceCandidates($candidates);
      }
      return NULL;
    }
    elseif ($plugin instanceof \crumbs_MonoPlugin_FindTitleInterface) {
      return $plugin->findTitle($path, $routerItem);
    }
    else {
      return NULL;
    }
  }

}
