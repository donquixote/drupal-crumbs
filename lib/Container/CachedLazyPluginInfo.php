<?php

abstract class crumbs_Container_CachedLazyPluginInfo {

  /**
   * @var array
   */
  protected $data = array();

  /**
   * @var array
   */
  protected $keysToCache = array();

  /**
   * The constructor.
   */
  function __construct() {
    $this->keysToCache = array_fill_keys($this->keysToCache(), TRUE);
  }

  /**
   * @return string[]
   */
  abstract protected function keysToCache();

  /**
   * Flush cached data.
   */
  function flushCaches() {
    $this->data = array();
    cache_clear_all('crumbs:', 'cache', TRUE);
  }

  /**
   * @param string $key
   *
   * @return mixed
   * @throws Exception
   */
  function __get($key) {
    if (array_key_exists($key, $this->data)) {
      return $this->data[$key];
    }

    return $this->data[$key] = empty($this->keysToCache[$key])
      ? $this->get($key)
      : $this->getCached($key);
  }

  /**
   * @param string $key
   *
   * @return mixed|false
   *   Any value except NULL.
   *
   * @throws Exception
   */
  private function getCached($key) {
    $cache = cache_get("crumbs:$key");
    if (isset($cache->data)) {
      // We do the serialization manually,
      // to prevent Drupal from intercepting exceptions.
      // However, from previous versions we might still have non-serialized data.
      return is_array($cache->data)
        ? $cache->data
        : unserialize($cache->data);
    }

    $data = $this->get($key);

    if (!is_array($data)) {
      throw new Exception("Only arrays can be cached in crumbs_CachedLazyPluginInfo.");
    }
    cache_set("crumbs:$key", serialize($data));
    return $data;
  }

  /**
   * @param string $key
   *
   * @return mixed|false
   *   Any value except NULL.
   *
   * @throws Exception
   */
  private function get($key) {
    $method = 'get_' . $key;
    if (!method_exists($this, $method)) {
      throw new Exception("Key $key not supported.");
    }
    $result = $this->$method($this);
    return isset($result)
      ? $result
      : FALSE;
  }

}
