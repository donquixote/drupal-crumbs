<?php

namespace Drupal\crumbs\PluginApi\Offset;

interface TreeOffsetMetaInterface {

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
