<?php

namespace Drupal\crumbs\PluginApi\Mapper\DefaultImplementation;

use Drupal\crumbs\PluginApi\Mapper\PluginFamilyInterface;

class PluginFamilyMapper extends RoutelessPluginMapper implements PluginFamilyInterface {

  /**
   * Set specific rules as disabled by default.
   *
   * @return $this
   */
  function disabledByDefault() {
    $this->parentPluginCollector->setDefaultStatus($this->prefix . '*', FALSE);
    $this->titlePluginCollector->setDefaultStatus($this->prefix . '*', FALSE);
    return $this;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindParent($description) {
    $this->parentPluginCollector->addDescription($this->prefix . '*', $description);
    return $this;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindTitle($description) {
    $this->titlePluginCollector->addDescription($this->prefix . '*', $description);
    return $this;
  }
}
