<?php

namespace Drupal\crumbs\PluginApi\Mapper;

interface MetaMapperInterface {

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
