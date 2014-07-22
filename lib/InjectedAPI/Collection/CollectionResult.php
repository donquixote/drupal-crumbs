<?php

/**
 * Represents the result of hook_crumbs_plugins()
 */
class crumbs_InjectedAPI_Collection_CollectionResult {

  /**
   * @var crumbs_InjectedAPI_Collection_PluginCollection
   */
  private $pluginCollection;

  /**
   * @var crumbs_InjectedAPI_Collection_DefaultValueCollection
   */
  private $defaultValueCollection;

  /**
   * @param crumbs_InjectedAPI_Collection_PluginCollection $pluginCollection
   * @param crumbs_InjectedAPI_Collection_DefaultValueCollection $defaultValueCollection
   */
  function __construct(
    crumbs_InjectedAPI_Collection_PluginCollection $pluginCollection,
    crumbs_InjectedAPI_Collection_DefaultValueCollection $defaultValueCollection
  ) {
    $this->pluginCollection = $pluginCollection;
    $this->defaultValueCollection = $defaultValueCollection;
  }

  /**
   * @return array
   * @throws Exception
   */
  function getPlugins() {
    return $this->pluginCollection->getPlugins();
  }

  /**
   * @return string[][]
   *   Format: $['findParent'][$plugin_key] = $method
   */
  function getRoutelessPluginMethods() {
    return $this->pluginCollection->getRoutelessPluginMethods();
  }

  /**
   * @return string[][][]
   *   Format: $['findParent'][$route][$plugin_key] = $method.
   */
  function getRoutePluginMethods() {
    return $this->pluginCollection->getRoutePluginMethods();
  }

  /**
   * @return string[][]
   *   Format: $[$pluginKey]['findParent'] = $method
   */
  function getPluginRoutelessMethods() {
    return $this->pluginCollection->getPluginRoutelessMethods();
  }

  /**
   * @return string[][][]
   *   Format: $[$pluginKey]['findParent'][$route] = $method
   */
  function getPluginRouteMethods() {
    return $this->pluginCollection->getPluginRouteMethods();
  }

  /**
   * @return mixed[]
   *   Format: $[$key] = false|$weight
   * @throws Exception
   */
  function getDefaultValues() {
    return $this->defaultValueCollection->getDefaultValues();
  }

} 
