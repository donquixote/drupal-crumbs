<?php

namespace Drupal\crumbs\PluginApi\Mapper\CallbackRecovery;

use Drupal\crumbs\PluginApi\Mapper\PluginFamilyInterface;

class CallbackFamilyMapper extends RoutelessCallbackMapper implements PluginFamilyInterface {

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
