<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Collection;

use Drupal\crumbs\PluginSystem\Discovery\DescribeMultiPluginArg;

class LabeledPluginCollection extends RawPluginCollection {

  /**
   * @var string[][]
   *   Format: $[$key][] = $description.
   */
  private $descriptions = array();

  /**
   * @var bool[]
   *   Format: $[$key] = TRUE.
   */
  private $leaves;

  /**
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   * @param string|null $route
   */
  function addMonoPlugin($key, \crumbs_MonoPlugin $plugin, $route = NULL) {
    parent::addMonoPlugin($key, $plugin, $route);
    $this->leaves[$key] = TRUE;
  }

  /**
   * @param string $key
   *   The plugin key, without the '.*'.
   * @param \crumbs_MultiPlugin $plugin
   * @param null $route
   */
  function addMultiPlugin($key, \crumbs_MultiPlugin $plugin, $route = NULL) {
    parent::addMultiPlugin($key, $plugin, $route);

    $api = new DescribeMultiPluginArg($key, $this);
    $descriptionOrNull = $plugin->describe($api);
    if (is_string($descriptionOrNull)) {
      $this->addDescription($key . '.*', $descriptionOrNull);
    }
  }

  /**
   * @param string $key
   * @param string $description
   */
  public function addDescription($key, $description) {
    if ('*' !== $key && '.*' !== substr($key, -2)) {
      $this->leaves[$key] = TRUE;
    }
    $this->descriptions[$key][] = $description;
  }

  /**
   * @param string $key
   * @param string $description
   *   The description in English.
   * @param string[] $args
   *   Placeholders to be inserted into the translated description.
   *
   * @see t()
   * @see format_string()
   */
  public function translateDescription($key, $description, $args = array()) {
    $this->addDescription($key, t($description, $args));
  }

  /**
   * @return string[]
   */
  public function getDescriptions() {
    $descriptions_merged = array();
    foreach ($this->descriptions as $key => $descriptions) {
      $description = array_shift($descriptions);
      if (!empty($descriptions)) {
        $description .= ' (' . implode(', ', $descriptions) . ')';
      }
      $descriptions_merged[$key] = $description;
    }
    return $descriptions_merged;
  }

  /**
   * @return bool[]
   *   Format: $[$key] = TRUE
   */
  public function getLeaves() {
    return $this->leaves;
  }

}
