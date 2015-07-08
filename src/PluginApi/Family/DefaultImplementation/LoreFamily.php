<?php

namespace Drupal\crumbs\PluginApi\Family\DefaultImplementation;

use Drupal\crumbs\PluginApi\Family\LoreFamilyInterface;

class LoreFamily extends Family implements LoreFamilyInterface {

  /**
   * Set specific rules as disabled by default.
   *
   * @return $this
   */
  function disabledByDefault() {
    $this->getFindTitleTreeNode()->disabledByDefault();
    $this->getFindParentTreeNode()->disabledByDefault();
    return $this;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindParent($description) {
    $this->getFindParentTreeNode()->describe($description);
    return $this;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindTitle($description) {
    $this->getFindTitleTreeNode()->describe($description);
    return $this;
  }
}
