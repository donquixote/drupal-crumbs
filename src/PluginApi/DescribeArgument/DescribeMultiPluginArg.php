<?php
namespace Drupal\crumbs\PluginApi\DescribeArgument;

use Drupal\crumbs\PluginSystem\Collection\PluginCollection\DescriptionCollectionInterface;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollectionInterface;

/**
 * Injected API object for the describe() method of multi plugins.
 */
class DescribeMultiPluginArg {

  /**
   * @var string
   *   The plugin key, without the '.*'.
   */
  private $pluginKey;

  /**
   * @var DescriptionCollectionInterface
   */
  private $descriptionCollection;

  /**
   * @param string $pluginKey
   *   The plugin key, without the '.*'.
   * @param \Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollectionInterface $treeCollection
   */
  function __construct($pluginKey, TreeCollectionInterface $treeCollection) {
    $this->pluginKey = $pluginKey;
    $this->descriptionCollection = $treeCollection;
  }

  /**
   * @param string $key_suffix
   * @param bool $title
   */
  function addRule($key_suffix, $title = TRUE) {
    $key = $this->pluginKey . '.' . $key_suffix;
    $this->descriptionCollection->addDescription($key, $title);
  }

  /**
   * @param string $key_suffix
   * @param string $title
   * @param string $label
   */
  function ruleWithLabel($key_suffix, $title, $label) {
    $this->addRule(
      $key_suffix, t(
        '!key: !value', array(
          '!key' => $label,
          '!value' => $title,
        )
      )
    );
  }

  /**
   * @param string $description
   * @param string $key_suffix
   */
  function addDescription($description, $key_suffix = '*') {
    $key = $this->pluginKey . '.' . $key_suffix;
    $this->descriptionCollection->addDescription($key, $description);
  }

  /**
   * @param string $untranslated
   * @param string[] $args
   */
  function translateDescription($untranslated, array $args = array()) {
    $key = $this->pluginKey . '.*';
    $this->descriptionCollection->translateDescription($key, $untranslated, $args);
  }

  /**
   * @param string $key_suffix
   * @param string $untranslated
   * @param string[] $args
   */
  function keyTranslateDescription($key_suffix, $untranslated, array $args = array()) {
    $key = $this->pluginKey . '.' . $key_suffix;
    $this->descriptionCollection->translateDescription($key, $untranslated, $args);
  }

  /**
   * @param array $paths
   * @param string $key_suffix
   *
   * @deprecated
   *   This method has no effect.
   */
  function setRoutes(array $paths, $key_suffix = '*') {
    // This method has no effect.
  }

  /**
   * @param string $description
   * @param string $label
   * @param string $key_suffix
   */
  function descWithLabel($description, $label, $key_suffix = '*') {
    $this->addDescription(
      t(
        '!key: !value', array(
          '!key' => $label,
          '!value' => $description,
        )
      ), $key_suffix
    );
  }
}
