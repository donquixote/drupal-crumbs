<?php


class crumbs_PluginSystem_PluginMethodIterator implements Iterator {

  /**
   * @var string[]
   */
  private $pluginMethods;

  /**
   * @var crumbs_PluginInterface[]
   */
  private $plugins;

  /**
   * @var string[]
   */
  private $pluginKeys = array();

  /**
   * The plugin key at the current iterator position.
   *
   * @var string|false
   */
  private $pluginKey;

  /**
   * The plugin method at the current iterator position.
   *
   * @var string
   */
  private $pluginMethod;

  /**
   * The plugin at the current iterator position.
   *
   * @var crumbs_PluginInterface
   */
  private $plugin;

  /**
   * @param string[] $pluginMethods
   *   Format: $[$plugin_key] = $method
   * @param crumbs_PluginInterface[] $plugins
   */
  function __construct($pluginMethods, $plugins) {
    $this->pluginMethods = $pluginMethods;
    $this->plugins = $plugins;
    $this->pluginKeys = array_keys($pluginMethods);

    // Set iterator start position.
    $this->setFirstValidIteratorPosition(reset($this->pluginKeys));
  }

  /**
   * @return bool
   *   TRUE, if the current plugin is a multi plugin.
   */
  function isMultiPlugin() {
    return $this->plugin instanceof crumbs_MultiPlugin;
  }

  /**
   * @return bool
   *   TRUE, if the current plugin is a mono plugin.
   */
  function isMonoPlugin() {
    return $this->plugin instanceof crumbs_MonoPlugin;
  }

  /**
   * @param mixed[] $args
   *   E.g. array($path, $item, $breadcrumb) for findTitle().
   *
   * @return mixed
   */
  function invokeFinderMethod(array $args) {
    return call_user_func_array(array($this->plugin, $this->pluginMethod), $args);
  }

  /**
   * @return $this
   */
  function current() {
    return $this;
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Return the key of the current element
   * @link http://php.net/manual/en/iterator.key.php
   *
   * @return string|null
   */
  public function key() {
    return $this->pluginKey;
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Checks if current position is valid
   * @link http://php.net/manual/en/iterator.valid.php
   *
   * @return boolean
   *   TRUE, if the current position is valid.
   */
  public function valid() {
    return NULL !== $this->pluginKey;
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Move forward to next element
   * @link http://php.net/manual/en/iterator.next.php
   */
  public function next() {
    $this->setFirstValidIteratorPosition(next($this->pluginKeys));
  }

  /**
   * Sets the iterator position. If the given position is not valid, it will
   * advance from there to the next valid position.
   *
   * @param string $pluginKey
   */
  private function setFirstValidIteratorPosition($pluginKey) {
    while (TRUE) {
      if ($pluginKey === FALSE) {
        // When next($array) returns false, Iterator::key() should return NULL.
        $this->pluginKey = NULL;
        $this->plugin = NULL;
        $this->pluginMethod = NULL;
        return;
      }
      if (isset($this->plugins[$pluginKey])) {
        $plugin = $this->plugins[$pluginKey];
        $method = $this->pluginMethods[$pluginKey];
        if (method_exists($plugin, $method)) {
          $this->pluginKey = $pluginKey;
          $this->plugin = $plugin;
          $this->pluginMethod = $method;
          return;
        }
      }
      $pluginKey = next($this->pluginKeys);
    }
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Rewind the Iterator to the first element
   * @link http://php.net/manual/en/iterator.rewind.php
   */
  public function rewind() {
    $this->pluginKey = reset($this->pluginKeys);
  }
}
