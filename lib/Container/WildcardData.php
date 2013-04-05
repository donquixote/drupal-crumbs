<?php

class crumbs_Container_WildcardData implements ArrayAccess {

  protected $data;
  protected $fallback;
  protected $prefixedContainers = array();

  /**
   * @param array $rule_weights
   *   Weights with wildcards, as saved in the configuration form.
   */
  function __construct(array $data = array()) {
    $this->data = $data;
    $this->fallback = isset($this->data['*']) ? $this->data['*'] : NULL;
  }

  function getAll($key) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    $values = array();
    while (!empty($fragments)) {
      $wildcard_key = $partial_key . '.*';
      if (isset($this->data[$wildcard_key])) {
        $values[$wildcard_key] = $this->data[$wildcard_key];
      }
      $partial_key .= '.'. array_shift($fragments);
    }
    if (isset($this->data[$key])) {
      $values[$key] = $this->data[$key];
    }
    return array_reverse($values);
  }

  function getAllMerged($key) {
    $merged = array();
    foreach ($this->getAll($key) as $values) {
      if (is_array($values)) {
        $merged = array_merge($merged, $values);
      }
    }
    return $merged;
  }

  function offsetGet($key) {
    return $this->resolve($key);
  }

  function offsetSet($key, $value) {
    $this->data[$key] = $value;
  }

  function offsetExists($key) {
    throw new Exception("offsetExists() not supported.");
  }

  function offsetUnset($key) {
    throw new Exception("offsetUnset() not supported.");
  }

  /**
   * Get a "child" container with a prefix.
   * E.g. if the config contains a weight setting "crumbs.nodeParent.* = 5"
   * then in the child keeper this will be just "nodeParent.* = 5".
   *
   * @param string $prefix
   *   The prefix.
   *
   * @return crumbs_Container_WildcardData
   *   The prefixed container.
   */
  function prefixedContainer($prefix) {
    if (!isset($this->prefixedContainers[$prefix])) {
      $data = $this->buildPrefixedData($prefix);
      $this->prefixedContainers[$prefix] = new self($data);
    }
    return $this->prefixedContainers[$prefix];
  }

  /**
   * Helper: Actually build the prefixed keeper.
   *
   * @param string $prefix
   *   Prefix, as above.
   *
   * @return crumbs_Container_WildcardData
   *   The prefixed container.
   */
  protected function buildPrefixedData($prefix) {
    $data = array();
    $k = strlen($prefix);
    $data[''] = $data['*'] = $this->wildcardValue($prefix);
    if (isset($this->data[$prefix])) {
      $data[''] = $this->data[$prefix];
    }
    if (isset($this->data[$prefix .'.*'])) {
      $data['*'] = $this->data[$prefix .'.*'];
    }
    foreach ($this->data as $key => $value) {
      if (strlen($key) > $k && substr($key, 0, $k+1) === ($prefix .'.')) {
        $data[substr($key, $k+1)] = $value;
      }
    }
    return $data;
  }

  /**
   * Determine the value for the rule specified by the key.
   *
   * @param string $key
   *   Key that we are looking for.
   *
   * @return mixed
   *   The value for this key.
   */
  function resolve($key = NULL) {
    if (!isset($key)) {
      // Find the top-level value.
      return $this->data[''];
    }
    if (isset($this->data[$key])) {
      // Look for explicit setting.
      return $this->data[$key];
    }
    // Try wildcards.
    return $this->wildcardValue($key);
  }

  /**
   * Helper: Resolve wildcards..
   *
   * @param string $key
   *   Key that we are looking for.
   *
   * @return mixed
   *   The value for this key.
   */
  function wildcardValue($key) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    $value = $this->fallback;
    while (!empty($fragments)) {
      if (isset($this->data[$partial_key .'.*'])) {
        $value = $this->data[$partial_key .'.*'];
      }
      $partial_key .= '.'. array_shift($fragments);
    }
    return $value;
  }
}
