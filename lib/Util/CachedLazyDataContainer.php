<?php

class crumbs_Util_CachedLazyDataContainer {

  protected $data = array();
  protected $source;
  protected $keysToCache = array();

  function __construct($source) {
    $this->source = $source;
    foreach ($source->keysToCache() as $key) {
      $this->keysToCache[$key] = TRUE;
    }
  }

  function __get($key) {
    if (!isset($this->data[$key])) {
      if (!empty($this->keysToCache[$key])) {
        $cache = cache_get("crumbs:$key");
        if (isset($cache->data)) {
          return $this->data[$key] = $cache->data;
        }
      }
      if (!method_exists($this->source, $key)) {
        throw new Exception("Key $key not supported.");
      }
      $result = $this->source->$key($this);
      $this->data[$key] = isset($result) ? $result : FALSE;
      cache_set("crumbs:$key", $this->data[$key]);
    }
    return $this->data[$key];
  }

  function __call($method, $args) {

    if (!isset($this->data[$method])) {
      $this->data[$method] = array();
      if (!empty($this->keysToCache[$method])) {
        $cache = cache_get("crumbs:$method");
        if (isset($cache->data) && is_array($cache->data)) {
          $this->data[$method] = $cache->data;
        }
      }
    }

    switch (count($args)) {

      case 1:
        if (!isset($this->data[$method][$args[0]])) {
          if (!method_exists($this->source, $method)) {
            throw new Exception("Method $method not supported.");
          }
          $result = $this->source->$method($this, $args[0]);
          $this->data[$method][$args[0]] = isset($result) ? $result : NULL;
          cache_set("crumbs:$method", $this->data[$method]);
        }
        return $this->data[$method][$args[0]];

      case 2:
        if (!isset($this->data[$method][$args[0]][$args[1]])) {
          if (!method_exists($this->source, $method)) {
            throw new Exception("Method $method not supported.");
          }
          $result = $this->source->$method($this, $args[0], $args[1]);
          $this->data[$method][$args[0]][$args[1]] = isset($result) ? $result : NULL;
          cache_set("crumbs:$method", $this->data[$method]);
        }
        return $this->data[$method][$args[0]][$args[1]];

      default:
        throw new Exception("Number of arguments not supported: " . count($args));
    }
  }
}
