<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset;

class EmptyArgumentOffset implements ArgumentOffsetInterface {

  /**
   * @param string $description
   *
   * @return $this
   */
  function describe($description) {
    // Do nothing
    return $this;
  }

  /**
   * @param string $description
   * @param string[] $args
   *
   * @return $this
   */
  function translateDescription($description, $args = array()) {
    // Do nothing
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
