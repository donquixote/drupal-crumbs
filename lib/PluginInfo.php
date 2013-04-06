<?php

/**
 * Data provider object to plug into a crumbs_Container_LazyData.
 */
class crumbs_PluginInfo {

  /**
   * Which keys to load from persistent cache.
   */
  function keysToCache() {
    return array('weights', 'pluginsCached', 'pluginOrder', 'basicPluginMethods', 'routePluginMethodsCached');
  }

  /**
   * Combination of user-defined weights and default weights
   */
  function weights($container) {
    $weights = $container->defaultWeights;
    foreach ($container->userWeights as $key => $weight) {
      // Make sure to skip NULL values.
      if (isset($weight)) {
        $weights[$key] = $weight;
      }
    }
    return $weights;
  }

  /**
   * Object that can calculate rule weights based on the weight settings.
   * (which are often wildcards)
   */
  function weightKeeper($container) {
    return new crumbs_Container_WildcardDataSorted($container->weights);
  }

  /**
   * Default weights without the user configuration
   */
  function defaultWeights($container) {
    $weights = array();
    foreach ($container->discovery['disabled_keys'] as $key) {
      $weights[$key] = FALSE;
    }
    return $weights;
  }

  /**
   * User-defined weights
   */
  function userWeights($container) {
    return variable_get('crumbs_weights', array(
      // TODO: This default value feels stupid. Why is it?
      'crumbs.home_title' => 0,
    ));
  }

  /**
   * Info from the plugins' describe() method, plus reflection info.
   * This is used on the weights form for the sake of information.
   */
  function adminPluginInfo($container) {

    $op = new crumbs_PluginOperation_describe();
    foreach ($container->plugins as $plugin_key => $plugin) {
      $op->invoke($plugin, $plugin_key);
    }
    return $op;
  }

  function adminAvailableKeys($container) {
    return $container->adminPluginInfo->getKeys();
  }

  function adminKeysByPlugin($container) {
    return $container->adminPluginInfo->getKeysByPlugin();
  }

  /**
   * Prepared list of plugins and methods for a given operation.
   */
  function basicPluginMethods($container, $method) {
    $type = ('decorateBreadcrumb' === $method) ? 'alter' : 'find';
    $result = array();
    foreach ($container->pluginsSorted[$type] as $plugin_key => $plugin) {
      if (method_exists($plugin, $method)) {
        $result[$plugin_key] = $method;
      }
    }
    return $result;
  }

  /**
   * Prepared list of plugins and methods for a given find operation and route.
   */
  function routePluginMethods($container, $method, $route) {
    $plugin_methods = $container->routePluginMethodsCached($method, $route);
    return (FALSE !== $plugin_methods) ? $plugin_methods : $container->basicPluginMethods($method);
  }

  /**
   * Prepared list of plugins and methods for a given find operation and route.
   * This is the version to be cached.
   */
  function routePluginMethodsCached($container, $method, $route) {
    $only_basic = TRUE;
    if (!empty($route)) {
      $method_suffix = crumbs_Util::buildMethodSuffix($route);
      if (!empty($method_suffix)) {
        $method_with_suffix = $method . '__' . $method_suffix;
        $result = array();
        foreach ($container->pluginOrder['find'] as $plugin_key => $weight) {
          $plugin = $container->plugins[$plugin_key];
          if (method_exists($plugin, $method_with_suffix)) {
            $result[$plugin_key] = $method_with_suffix;
            $only_basic = FALSE;
          }
          elseif (method_exists($plugin, $method)) {
            $result[$plugin_key] = $method;
          }
        }
      }
    }
    return $only_basic ? FALSE : $result;
  }

  /**
   * Plugins, not sorted, but already with the weights information.
   */
  function plugins($container) {
    // We use a trick to always include the plugin files, even if the plugins
    // are coming from the cache.
    $container->includePluginFiles;
    return $container->pluginsCached;
  }

  /**
   * Plugins, not sorted, but already with the weights information.
   */
  function pluginsCached($container) {
    $plugins = $container->discovery['plugins'];
    foreach ($plugins as $plugin_key => $plugin) {
      // Let plugins know about the weights, if they want to.
      if (method_exists($plugin, 'initWeights')) {
        $plugin->initWeights($container->weightKeeper->prefixedContainer($plugin_key));
      }
    }
    return $plugins;
  }

  /**
   * Information returned from hook_crumbs_plugins()
   */
  function discovery($container) {
    $container->includePluginFiles;
    $plugins = array();
    $disabled_keys = array();
    $api = new crumbs_InjectedAPI_hookCrumbsPlugins($plugins, $disabled_keys);
    foreach (module_implements('crumbs_plugins') as $module) {
      $function = $module .'_crumbs_plugins';
      $api->setModule($module);
      $function($api);
    }
    $api->finalize();
    return compact('plugins', 'disabled_keys');
  }

  /**
   * Order of plugins, for 'find' and 'alter' operations.
   */
  function pluginOrder($container) {

    $order = array(
      'find' => array(),
      'alter' => array(),
    );

    // Sort the plugins, using the weights from weight keeper.
    $weight_keeper = $container->weightKeeper;
    foreach ($container->plugins as $plugin_key => $plugin) {
      if ($plugin instanceof crumbs_MultiPlugin) {
        $keeper = $weight_keeper->prefixedContainer($plugin_key);
        $w_find = $keeper->smallestValue();
        if ($w_find !== FALSE) {
          $order['find'][$plugin_key] = $w_find;
        }
        // Multi plugins cannot participate in alter operations.
      }
      else {
        $weight = $weight_keeper->valueAtKey($plugin_key);
        if ($weight !== FALSE) {
          $order['find'][$plugin_key] = $weight;
          $order['alter'][$plugin_key] = $weight;
        }
      }
    }

    // Lowest weight first = highest priority first
    asort($order['find']);

    // Lowest weight last = highest priority last
    arsort($order['alter']);

    return $order;
  }

  /**
   * Sorted plugins for 'find' and 'alter' operations.
   */
  function pluginsSorted($container) {
    $sorted = $container->pluginOrder;
    $plugins = $container->plugins;
    foreach (array('find', 'alter') as $type) {
      foreach ($sorted[$type] as $plugin_key => &$x) {
        $x = $plugins[$plugin_key];
      }
    }
    return $sorted;
  }

  /**
   * Include files in the /plugin/ folder.
   * We use the cache mechanic to make sure this happens exactly once.
   */
  function includePluginFiles($container) {

    $modules = array(
      'blog',
      'comment',
      'crumbs',
      'entityreference',
      'menu',
      'path',
      'taxonomy',
      'forum',
      'entityreference_prepopulate'
    );

    // Include Crumbs-provided plugins.
    foreach ($modules as $module) {
      if (module_exists($module)) {
        module_load_include('inc', 'crumbs', 'plugins/crumbs.'. $module);
      }
    }

    // Organic groups is a special case,
    // because 7.x-2.x behaves different from 7.x-1.x.
    if (module_exists('og')) {
      if (function_exists('og_get_group')) {
        // We are using the og-7.x-1.x branch.
        module_load_include('inc', 'crumbs', 'plugins/crumbs.og');
      }
      else {
        // We are using the og-7.x-2.x branch.
        module_load_include('inc', 'crumbs', 'plugins/crumbs.og.2');
      }
    }

    return TRUE;
  }
}
