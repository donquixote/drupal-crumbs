<?php

class crumbs_InjectedAPI_Collection_MetaCollection {

  /**
   * @var array
   */
  protected $keys = array('*' => TRUE);

  /**
   * @var array
   */
  protected $keysByPlugin = array();

  /**
   * @var string[][]
   *   Format: $[$key][] = $description
   */
  private $descriptions = array();

  /**
   * @param string $key
   * @param string $description
   */
  function addDescription($key, $description) {
    $this->descriptions[$key][] = $description;
  }

  /**
   * @param string $pluginKey
   * @param string $key
   */
  function addPluginRule($pluginKey, $key) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    while (TRUE) {
      if (empty($fragments)) break;
      $wildcard_key = $partial_key .'.*';
      $this->keys[$wildcard_key] = TRUE;
      $this->keysByPlugin[$pluginKey][$wildcard_key] = TRUE;
      $partial_key .= '.'. array_shift($fragments);
    }
    $this->keys[$key] = $key;
    $this->keysByPlugin[$pluginKey][$key] = $key;
  }

  /**
   * @param string $key
   * @param int $weight
   */
  function setDefaultWeight($key, $weight) {
    $this->keys[$key] = $key;
  }

  /**
   * @return array
   */
  function getKeys() {
    return $this->keys;
  }

  /**
   * @return array
   */
  function getKeysByPlugin() {
    return $this->keysByPlugin;
  }

  /**
   * @return crumbs_Container_MultiWildcardData
   */
  function collectedInfo() {
    $container = new crumbs_Container_MultiWildcardData($this->keys);
    $container->__set('key', $this->keys);
    $container->descriptions = $this->descriptions;
    return $container;
  }
}
