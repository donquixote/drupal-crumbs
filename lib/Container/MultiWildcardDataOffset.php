<?php

/**
 * Class crumbs_Container_MultiWildcardDataOffset
 *
 * @property array $basicMethods
 * @property array $routeMethods
 * @property array $routes
 * @property string[] $descriptions
 */
class crumbs_Container_MultiWildcardDataOffset {

  /**
   * @var crumbs_Container_MultiWildcardData
   */
  protected $container;

  /**
   * @var string
   *   A plugin result key, e.g. "menu.hierarchy.*".
   */
  protected $key;

  /**
   * @param crumbs_Container_MultiWildcardData $container
   * @param string $key
   *   A plugin result key, e.g. "menu.hierarchy.*".
   */
  function __construct($container, $key) {
    $this->container = $container;
    $this->key = $key;
  }

  /**
   * @param string $key
   *   A meta property name, such as "basicMethods" or "descriptions"
   *
   * @return mixed
   *   Value of the property.
   */
  function __get($key) {
    return $this->container->__get($key)->valueAtKey($this->key);
  }

  /**
   * @param string $key
   *   A meta property name, such as "basicMethods" or "descriptions"
   *
   * @return array
   */
  function getAll($key) {
    return $this->container->__get($key)->getAll($this->key);
  }

  /**
   * @param string $key
   * @return array
   */
  function getAllMerged($key) {
    return $this->container->__get($key)->getAllMerged($this->key);
  }

  /**
   * @return string
   *   The plugin priority key, e.g. menu.hierarchy.*
   */
  function getKey() {
    return $this->key;
  }

  /**
   * @return string|NULL
   *   The parent plugin priority key, e.g. menu.* for menu.hierarchy.*
   */
  function getParentKey() {
    if ('*' === $this->key) {
      return NULL;
    }
    $key = ('.*' !== substr($this->key, -2))
      ? $this->key
      : substr($this->key, 0, -2);
    return (FALSE === $pos = strrpos($key, '.'))
      ? '*'
      : substr($key, 0, $pos) . '.*';
  }
}
