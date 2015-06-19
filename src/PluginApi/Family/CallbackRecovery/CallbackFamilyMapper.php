<?php

namespace Drupal\crumbs\PluginApi\Family\CallbackRecovery;

use Drupal\crumbs\PluginApi\Family\FamilyLoreInterface;

class CallbackFamilyMapper extends RoutelessCallbackMapper implements FamilyLoreInterface {

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindParent($description) {
    // Do nothing.
    return $this;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindTitle($description) {
    // Do nothing.
    return $this;
  }

  /**
   * @return $this
   */
  function disabledByDefault() {
    // Do nothing.
    return $this;
  }
}
