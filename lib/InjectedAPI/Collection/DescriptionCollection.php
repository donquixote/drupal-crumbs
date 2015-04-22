<?php

class crumbs_InjectedAPI_Collection_DescriptionCollection {

  /**
   * Descriptions for plugin keys.
   *
   * @var array
   *   Format: $['menu.hierarchy.*'] = t('Menu hierarchy');
   */
  private $descriptions = array();

  /**
   * @param string $key
   * @param string $description
   */
  function setDescription($key, $description) {
    $this->descriptions[$key] = $description;
  }
}
