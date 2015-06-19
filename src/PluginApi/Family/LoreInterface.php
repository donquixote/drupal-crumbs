<?php

namespace Drupal\crumbs\PluginApi\Family;

interface LoreInterface {

  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindParent($description);
  /**
   * @param string $description
   *
   * @return $this
   */
  function describeFindTitle($description);

  /**
   * @return $this
   */
  function disabledByDefault();

}
