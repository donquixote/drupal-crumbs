<?php

namespace Drupal\crumbs\PluginApi\Aggregate;

use Drupal\crumbs\PluginSystem\PluginType\ParentPluginType;
use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;
use Drupal\crumbs\PluginSystem\PluginType\TitlePluginType;

class EntityRoute implements EntityRouteInterface {

  /**
   * @var string
   */
  protected $typeName;

  /**
   * @var string
   */
  protected $bundleKey;

  /**
   * @var string
   */
  protected $bundleLabel;

  /**
   * @param string $typeName
   * @param string $bundleKey
   * @param string $bundleLabel
   */
  function __construct($typeName, $bundleKey, $bundleLabel) {
    $this->typeName = $typeName;
    $this->bundleKey = $bundleKey;
    $this->bundleLabel = $bundleLabel;
  }

  /**
   * @return string
   */
  public function getEntityType() {
    return $this->typeName;
  }

  /**
   * @param \crumbs_EntityPlugin $entityPlugin
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   *
   * @return \crumbs_MultiPlugin
   * @throws \Exception
   */
  public function createPlugin(\crumbs_EntityPlugin $entityPlugin, PluginTypeInterface $pluginType) {
    if ($pluginType instanceof ParentPluginType) {
      return new \crumbs_MultiPlugin_EntityParent($entityPlugin, $this->typeName, $this->bundleKey, $this->bundleLabel);
    }
    elseif ($pluginType instanceof TitlePluginType) {
      return new \crumbs_MultiPlugin_EntityTitle($entityPlugin, $this->typeName, $this->bundleKey, $this->bundleLabel);
    }
    else {
      throw new \Exception("Invalid entity type.");
    }
  }
}
