<?php

namespace Drupal\crumbs_ui\PluginKey;

interface PluginKeyInterface {

  /**
   * @return bool
   *   TRUE, if this plugin key is disabled by default.
   *   FALSE, otherwise.
   */
  public function isDisabledByDefault();

  /**
   * @return \Drupal\crumbs_ui\PluginKey\PluginKeyInterface|NULL
   */
  public function getParentKey();

  /**
   * @return string
   */
  public function __toString();

  /**
   * @return \Drupal\crumbs_ui\PluginKey\PluginKeyInterface[]
   */
  public function getChildren();

  /**
   * Checks if the plugin key is a wildcard and may theoretically have children.
   *
   * This returns true even if the plugin key currently has no children.
   *
   * @return bool
   *   TRUE, if the plugin key is a wildcard.
   *   FALSE, otherwise.
   */
  public function isWildcardKey();

  /**
   * @return bool
   *   TRUE, if the plugin has an explicit value.
   *   FALSE, if the plugin value is inherited from the parent.
   */
  public function hasExplicitValue();

  /**
   * @return bool
   *   TRUE, if the plugin is enabled (explicitly, or via inheritance).
   *   FALSE, otherwise.
   */
  public function isEnabled();

  /**
   * @return string
   */
  public function getLongDescription();

  /**
   * @return int|FALSE
   *   A numeric weight, if the plugin key is enabled.
   *   FALSE, otherwise.
   */
  public function getWeight();
}
