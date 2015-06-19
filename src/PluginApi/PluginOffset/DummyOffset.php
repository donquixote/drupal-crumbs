<?php

namespace Drupal\crumbs\PluginApi\PluginOffset;

class DummyOffset implements TreeOffsetMetaInterface {

  /**
   * @param string $description
   *
   * @return $this
   */
  function describe($description) {
    // Ignore.
  }

  /**
   * @param string $description
   * @param string[] $args
   *
   * @return $this
   */
  function translateDescription($description, $args = array()) {
    // Ignore.
  }

  /**
   * @return $this
   */
  function disabledByDefault() {
    // Ignore.
  }
}
