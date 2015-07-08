<?php
namespace Drupal\crumbs\PluginApi\DescribeArgument;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;

/**
 * Argument to be passed into \crumbs_MultiPlugin::describe()
 *
 * @see \crumbs_MultiPlugin::describe().
 */
class DescribeMultiPluginArg {

  /**
   * @var \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  private $treeNode;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $treeNode
   */
  function __construct(TreeNode $treeNode) {
    $this->treeNode = $treeNode;
  }

  /**
   * @param string $key
   * @param bool $title
   */
  function addRule($key, $title = TRUE) {
    $this->treeNode->child($key)->describe($title);
  }

  /**
   * @param string $key
   * @param string $title
   * @param string $description
   */
  function ruleWithLabel($key, $title, $description) {
    $this->treeNode->child($key)->translateDescription(
      '!key: !value',
      array(
        '!key' => $description,
        '!value' => $title,
      ));
  }

  /**
   * @param string $description
   * @param string|null $key
   */
  function addDescription($description, $key = NULL) {
    $this->treeNode->child($key)->describe($description);
  }

  /**
   * @param string $untranslated
   * @param string[] $args
   */
  function translateDescription($untranslated, array $args = array()) {
    $this->treeNode->translateDescription($untranslated, $args);
  }

  /**
   * @param string $key
   * @param string $untranslated
   * @param string[] $args
   */
  function keyTranslateDescription($key, $untranslated, array $args = array()) {
    $this->treeNode->child($key)->translateDescription($untranslated, $args);
  }

  /**
   * @param array $paths
   * @param string $key
   *
   * @deprecated
   *   This method has no effect.
   */
  function setRoutes(array $paths, $key = NULL) {
    // This method has no effect.
  }

  /**
   * @param string $description
   * @param string $label
   * @param string|null $key
   */
  function descWithLabel($description, $label, $key = NULL) {
    $this->treeNode->child($key)->translateDescription(
      '!key: !value',
      array(
        '!key' => $label,
        '!value' => $description,
      ));
  }
}
