<?php

namespace Drupal\crumbs_ui\PluginKey;

use crumbs_Container_MultiWildcardDataOffset;

class PluginKey implements PluginKeyInterface {

  /**
   * @var \crumbs_Container_MultiWildcardDataOffset
   */
  private $offset;

  /**
   * @var \Drupal\crumbs_ui\PluginKey\StatefulHierarchyInterface
   */
  private $hierarchy;

  /**
   * @var string
   */
  private $pluginKeyStr;

  /**
   * @param string $plugin_key_str
   * @param \crumbs_Container_MultiWildcardDataOffset $offset
   * @param \Drupal\crumbs_ui\PluginKey\StatefulHierarchyInterface $hierarchy
   */
  function __construct($plugin_key_str, \crumbs_Container_MultiWildcardDataOffset $offset, StatefulHierarchyInterface $hierarchy) {
    $this->pluginKeyStr = $plugin_key_str;
    $this->offset = $offset;
    $this->hierarchy = $hierarchy;
  }

  /**
   * @return bool
   *   TRUE, if this plugin key is disabled by default.
   *   FALSE, otherwise.
   */
  public function isDisabledByDefault() {
    // TODO: Implement isDisabledByDefault() method.
    # return $this->hierarchy->keyIsDisabledByDefault($this->pluginKeyStr);
  }

  /**
   * @return \Drupal\crumbs_ui\PluginKey\PluginKeyInterface|NULL
   */
  public function getParentKey() {
    return $this->hierarchy->keyGetParentKey($this->pluginKeyStr);
  }

  /**
   * @return string
   */
  public function __toString() {
    return $this->pluginKeyStr;
  }

  /**
   * @return \Drupal\crumbs_ui\PluginKey\PluginKeyInterface[]
   */
  public function getChildren() {
    return $this->hierarchy->keyGetChildren($this->pluginKeyStr);
  }

  /**
   * Checks if the plugin key is a wildcard and may theoretically have children.
   *
   * This returns true even if the plugin key currently has no children.
   *
   * @return bool
   *   TRUE, if the plugin key is a wildcard.
   *   FALSE, otherwise.
   */
  public function isWildcardKey() {
    return '*' === $this->pluginKeyStr || '.*' === substr($this->pluginKeyStr, -2);
  }

  /**
   * @return bool
   *   TRUE, if the plugin has an explicit value.
   *   FALSE, if the plugin value is inherited from the parent.
   */
  public function hasExplicitValue() {
    return $this->hierarchy->keyHasExplicitValue($this->pluginKeyStr);
  }

  /**
   * @return bool
   *   TRUE, if the plugin key is enabled (explicitly, or via inheritance).
   *   FALSE, otherwise.
   */
  public function isEnabled() {
    return $this->hierarchy->keyIsEnabled($this->pluginKeyStr);
  }

  /**
   * @return string
   */
  public function getLongDescription() {
    $parent_key = $this->getParentKey();
    if (!isset($parent_key)) {
      return t('Top-level wildcard plugin key.') . '<br/>'
        . t('Provides the default status and weight for all plugin keys that do not have a more specific setting.');
    }
    if ('*' === $parent_key->__toString()) {
      if ($this->isWildcardKey()) {
        return t('Provides a default status and weight for all plugin keys provided by the !module_name module.', array(
          '!module_name' => substr($this->pluginKeyStr, 0, -2),
        ));
      }
      else {
        return t('The only plugin provided by the !module_name module.', array(
          '!module_name' => $this->pluginKeyStr,
        ));
      }
    }
    $desc_html = '';
    foreach ($this->offset->descriptions ?: array() as $description) {
      $desc_html .= '<p>' . $description . '</p>';
    }
    return $desc_html;
  }

  /**
   * @return int|FALSE
   *   A numeric weight, if the plugin key is enabled.
   *   FALSE, otherwise.
   */
  public function getWeight() {
    return $this->hierarchy->keyGetWeight($this->pluginKeyStr);
  }
}
