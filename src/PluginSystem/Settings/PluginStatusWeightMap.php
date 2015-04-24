<?php

namespace Drupal\crumbs\PluginSystem\Settings;

use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;

class PluginStatusWeightMap {

  /**
   * @var mixed[]
   */
  private $merged;

  /**
   * @param bool[] $defaultStatuses
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   *
   * @return \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap
   */
  static function loadAndCreate(array $defaultStatuses, PluginTypeInterface $pluginType) {
    $settings = variable_get($pluginType->getSettingsKey(), array()) + array(
      'statuses' => array(),
      'weights' => array(),
    );
    $statuses = $settings['statuses'] + $defaultStatuses + array('*' => TRUE);
    $weights = $settings['weights'] + array('*' => 0);
    return static::create($statuses, $weights);
  }

  /**
   * @param bool[] $statuses
   * @param int[] $weights
   *
   * @return static
   */
  static function create(array $statuses, array $weights) {
    if (!isset($statuses['*'])) {
      throw new \InvalidArgumentException("Missing fallback status.");
    }
    if (!isset($weights['*'])) {
      throw new \InvalidArgumentException("Missing fallback weight.");
    }
    $merged = static::mergeStatusesAndWeights($statuses, $weights);
    return new static($merged);
  }

  /**
   * @param bool[] $statuses
   * @param int[] $weights
   *
   * @return mixed[]
   */
  static function mergeStatusesAndWeights(array $statuses, array $weights) {
    $statusKeyMap = new KeyMap($statuses);
    $weightKeyMap = new KeyMap($weights);
    $merged = array();
    foreach ($statuses as $statusKey => $status) {
      if (FALSE === $status) {
        $merged[$statusKey] = FALSE;
      }
      else {
        $weightKey = $weightKeyMap->keyLookup($statusKey);
        $merged[$statusKey] = $weights[$weightKey];
      }
    }
    foreach ($weights as $weightKey => $weight) {
      $statusKey = $statusKeyMap->keyLookup($weightKey);
      if (FALSE !== $statuses[$statusKey]) {
        $merged[$weightKey] = $weight;
      }
    }
    return $merged;
  }

  /**
   * @param mixed[] $merged
   */
  function __construct(array $merged) {
    $this->merged = $merged;
    $this->keyMap = new KeyMap($merged);
  }

  /**
   * @param string $key
   *
   * @return int|false
   */
  public function keyGetWeightOrFalse($key) {
    $key = $this->keyMap->keyLookup($key);
    return $this->merged[$key];
  }

  /**
   * @param string $prefix
   *   A key prefix, without and '.*'.
   *
   * @return PluginStatusWeightMap
   */
  public function getLocalStatusMap($prefix) {
    $pattern = $prefix . '.';
    $length = strlen($pattern);
    $localMerged = array();
    foreach ($this->merged as $key => $value) {
      if ($pattern === substr($key, 0, $length)) {
        $localMerged[substr($key, $length + 1)] = $value;
      }
    }
    $fallbackKey = $this->keyMap->keyLookup($prefix . '.*');
    $localMerged['*'] = $this->merged[$fallbackKey];
    return new static($localMerged);
  }

  /**
   * @return int[]
   */
  public function getDistinctWeights() {
    $weights = array();
    foreach ($this->merged as $key => $weightOrFalse) {
      if (FALSE !== $weightOrFalse) {
        $weights[$weightOrFalse] = TRUE;
      }
    }
    return array_keys($weights);
  }

}
