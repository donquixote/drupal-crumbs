<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset;

interface ArgumentOffsetInterface {

  /**
   * @param string $description
   *
   * @return $this
   */
  function describe($description);

  /**
   * @param string $description
   * @param string[] $args
   *
   * @return $this
   */
  function translateDescription($description, $args = array());

  /**
   * @return $this
   */
  function disabledByDefault();
}
