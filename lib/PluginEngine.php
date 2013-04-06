<?php


class crumbs_PluginEngine {

  protected $pluginInfo;
  protected $plugins;
  protected $weightKeeper;

  /**
   * @param array $plugins
   *   Plugins, not sorted.
   * @param crumbs_Container_WildcardDataSorted $weight_keeper
   *   A container that can determine a weight for every plugin rule.
   */
  function __construct($plugin_info) {
    $this->pluginInfo = $plugin_info;
    // These are for quicker access.
    $this->plugins = $plugin_info->plugins;
    $this->weightKeeper = $plugin_info->weightKeeper;
  }

  /**
   * Ask applicable plugins to "decorate" (alter) the breadcrumb.
   */
  function decorateBreadcrumb($breadcrumb) {
    $plugin_methods = $this->pluginInfo->basicPluginMethods('decorateBreadcrumb');
    foreach ($plugin_methods as $plugin_key => $method) {
      $plugin = $this->plugins[$plugin_key];
      if (!method_exists($plugin, 'decorateBreadcrumb')) {
        // This means the code has changed, without the cache being cleared.
        // It is the user's responsibility to clear the cache.
        // Until then, we simply ignore and move on.
        continue;
      }
      $plugin->decorateBreadcrumb($breadcrumb);
    }
  }

  /**
   * Invoke all relevant plugins to find the parent for a given path.
   *
   * @param string $path
   * @param array $item
   */
  function findParent($path, $item, &$all_candidates = array(), &$best_candidate_key = NULL) {
    $plugin_methods = $this->pluginInfo->routePluginMethods('findParent', $item['route']);
    $result = $this->find($plugin_methods, array($path, $item), TRUE, $all_candidates, $best_candidate_key);
    return $result;
  }

  protected function processFindParent($parent_raw) {
    if (url_is_external($parent_raw)) {
      // Always discard external paths.
      return NULL;
    }
    return drupal_get_normal_path($parent_raw);
  }

  /**
   * Invoke all relevant plugins to find the title for a given path.
   *
   * @param string $path
   * @param array $item
   * @param array $breadcrumb
   */
  function findTitle($path, $item, $breadcrumb, &$all_candidates = array(), &$best_candidate_key = NULL) {
    $plugin_methods = $this->pluginInfo->routePluginMethods('findTitle', $item['route']);
    $result = $this->find($plugin_methods, array($path, $item, $breadcrumb), FALSE, $all_candidates, $best_candidate_key);
    return $result;
  }

  /**
   * Invoke all relevant plugins to find title or parent for a given path.
   *
   * @param array $plugin_methods
   * @param array $args
   * @param array &$all_candidates
   *   Collect information during the operation.
   * @param string &$best_candidate_key
   */
  protected function find($plugin_methods, $args, $processFindParent = FALSE, &$all_candidates = array(), &$best_candidate_key = NULL) {
    $best_candidate = NULL;
    $best_candidate_weight = 999999;
    foreach ($plugin_methods as $plugin_key => $method) {
      $plugin = $this->plugins[$plugin_key];
      if ($plugin instanceof crumbs_MultiPlugin) {
        // That's a MultiPlugin
        $keeper = $this->weightKeeper->prefixedContainer($plugin_key);
        if ($best_candidate_weight <= $keeper->smallestValue()) {
          return $best_candidate;
        }
        if (!method_exists($plugin, $method)) {
          // This means the code has changed, without the cache being cleared.
          // It is the user's responsibility to clear the cache.
          // Until then, we simply ignore and move on.
          continue;
        }
        $candidates = call_user_func_array(array($plugin, $method), $args);
        if (!empty($candidates)) {
          foreach ($candidates as $candidate_key => $candidate_raw) {
            if (isset($candidate_raw)) {
              $candidate_weight = $keeper->valueAtKey($candidate_key);
              $candidate = $processFindParent ? $this->processFindParent($candidate_raw) : $candidate_raw;
              $all_candidates["$plugin_key.$candidate_key"] = array($candidate_weight, $candidate_raw, $candidate);
              if ($best_candidate_weight > $candidate_weight && isset($candidate)) {
                $best_candidate = $candidate;
                $best_candidate_weight = $candidate_weight;
                $best_candidate_key = $candidate_key;
              }
            }
          }
        }
      }
      elseif ($plugin instanceof crumbs_MonoPlugin) {
        // That's a MonoPlugin
        $candidate_weight = $this->weightKeeper->valueAtKey($plugin_key);
        if ($best_candidate_weight <= $candidate_weight) {
          return $best_candidate;
        }
        if (!method_exists($plugin, $method)) {
          // This means the code has changed, without the cache being cleared.
          // It is the user's responsibility to clear the cache.
          // Until then, we simply ignore and move on.
          continue;
        }
        $candidate_raw = call_user_func_array(array($plugin, $method), $args);
        if (isset($candidate_raw)) {
          $candidate = $processFindParent ? $this->processFindParent($candidate_raw) : $candidate_raw;
          $all_candidates[$plugin_key] = array($candidate_weight, $candidate_raw, $candidate);
          if (isset($candidate)) {
            $best_candidate = $candidate;
            $best_candidate_weight = $candidate_weight;
            $best_candidate_key = $plugin_key;
          }
        }
      }
    }
    return $best_candidate;
  }
}
