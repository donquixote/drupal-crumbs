<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Buffer;

use Drupal\crumbs\PluginApi\Collector\Implementation\PrimaryPluginCollector;
use Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery;
use Drupal\crumbs\PluginSystem\PluginType\ParentPluginType;
use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;
use Drupal\crumbs\PluginSystem\PluginType\TitlePluginType;

class PluginDiscoveryBuffer {

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface|NULL
   */
  protected $parentCollector;

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface|NULL
   */
  protected $titleCollector;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery
   */
  protected $pluginDiscovery;

  /**
   * @return static
   */
  static function create() {
    $discovery = PluginDiscovery::create();
    return new static($discovery);
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery $pluginDiscovery
   */
  function __construct(PluginDiscovery $pluginDiscovery) {
    $this->pluginDiscovery = $pluginDiscovery;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Collection\PluginCollection\RawPluginCollection
   */
  function getParentCollector() {
    $this->load();
    return $this->parentCollector;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Collection\PluginCollection\RawPluginCollection
   */
  function getTitleCollector() {
    $this->load();
    return $this->titleCollector;
  }

  protected function load() {
    if (!isset($this->parentCollector)) {
      $this->parentCollector = new PrimaryPluginCollector(TRUE);
      $this->titleCollector = new PrimaryPluginCollector(FALSE);
      $this->pluginDiscovery->discoverPlugins($this->parentCollector, $this->titleCollector);
    }
  }

  function reset() {
    $this->parentCollector = NULL;
    $this->titleCollector = NULL;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   *
   * @return \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface|NULL
   */
  public function getCollector(PluginTypeInterface $pluginType) {
    if ($pluginType instanceof TitlePluginType) {
      return $this->titleCollector;
    }
    elseif ($pluginType instanceof ParentPluginType) {
      return $this->parentCollector;
    }

    throw new \InvalidArgumentException("Invalid plugin type.");
  }
}
