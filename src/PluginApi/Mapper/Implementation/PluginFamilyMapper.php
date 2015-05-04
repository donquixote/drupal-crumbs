<?php

namespace Drupal\crumbs\PluginApi\Mapper\Implementation;

use Drupal\crumbs\PluginApi\Mapper\PluginFamilyInterface;

class PluginFamilyMapper extends PrimaryPluginMapper implements PluginFamilyInterface {

  /**
   * Set specific rules as disabled by default.
   *
   * @return $this
   */
  function disabledByDefault() {
    $this->parentCollectionContainer->setDefaultStatus($this->prefix . '*', FALSE);
    $this->titleCollectionContainer->setDefaultStatus($this->prefix . '*', FALSE);
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindParent($description) {
    $this->parentCollectionContainer->addDescription($this->prefix . '*', $description);
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindTitle($description) {
    $this->titleCollectionContainer->addDescription($this->prefix . '*', $description);
  }
}
