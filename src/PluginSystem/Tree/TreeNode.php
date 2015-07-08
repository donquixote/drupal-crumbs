<?php

namespace Drupal\crumbs\PluginSystem\Tree;

use Drupal\crumbs\PluginApi\Aggregate\EntityPluginAggregate;
use Drupal\crumbs\PluginApi\DescribeArgument\DescribeMultiPluginArg;
use Drupal\crumbs\PluginApi\Offset\TreeOffset;
use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;

class TreeNode extends TreeNodeBase {

  /**
   * @var \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface
   */
  private $pluginType;

  /**
   * @var \crumbs_PluginInterface|null
   */
  private $plugin;

  /**
   * @var \crumbs_PluginInterface[]
   *   Format: $[$route] = $plugin
   */
  private $routePlugins = array();

  /**
   * @var EntityPluginAggregate|null
   */
  private $entityPluginAggregate;

  /**
   * The (already translated) description.
   *
   * @var string|null
   */
  private $description;

  /**
   * The untranslated description.
   *
   * @var string|null
   */
  private $rawDescription;

  /**
   * Translation arguments for description.
   *
   * @var string[]
   */
  private $descriptionArgs = array();

  /**
   * The default status.
   *
   * @var bool|null
   *   FALSE for "Disabled by default", NULL otherwise.
   */
  private $status;

  /**
   * The weight at this tree node.
   *
   * @var int|null
   */
  private $weight;

  /**
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   *
   * @return static
   */
  static function root(PluginTypeInterface $pluginType) {
    $tree = new static($pluginType, FALSE);
    $tree->status = TRUE;
    $tree->weight = 0;
    return $tree;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   * @param bool|null $isLeaf
   */
  function __construct(PluginTypeInterface $pluginType, $isLeaf = NULL) {
    $this->pluginType = $pluginType;
    parent::__construct($isLeaf);
  }

  /**
   * Adds real plugins for the entity plugins.
   *
   * @param \Drupal\crumbs\PluginApi\Aggregate\EntityRouteInterface[] $entityRoutes
   */
  function unfoldEntityPlugins(array $entityRoutes) {
    if (!empty($this->entityPluginAggregate)) {
      $this->entityPluginAggregate->finalize($this, $entityRoutes, $this->pluginType);
      $this->entityPluginAggregate = NULL;
    }
    else {
      foreach ($this->getChildren() as $key => $child) {
        $child->unfoldEntityPlugins($entityRoutes);
      }
    }
  }

  /**
   * @param bool|null $status
   */
  function setStatus($status) {
    if (isset($status)) {
      $this->status = $status;
    }
  }

  /**
   * @param mixed[] $statuses
   *
   * @throws \Exception
   */
  function setStatuses(array $statuses) {
    if (isset($statuses['*'])) {
      $this->status = $statuses['*'];
      unset($statuses['*']);
    }
    foreach ($statuses as $key => $status_es) {
      $child = $this->child($key);
      if (is_array($status_es)) {
        $child->setStatuses($status_es);
      }
      elseif (isset($status_es)) {
        $child->setStatus($status_es);
      }
    }
  }

  /**
   * @return bool|null
   */
  function getStatus() {
    return $this->status;
  }

  /**
   * @param int|null $weight
   */
  function setWeight($weight) {
    if (isset($weight)) {
      $this->weight = $weight;
    }
  }

  /**
   * @param mixed[] $weights
   *
   * @throws \Exception
   */
  function setWeights(array $weights) {
    if (isset($weights['*'])) {
      $this->weight = $weights['*'];
      unset($weights['*']);
    }
    foreach ($weights as $key => $weight_s) {
      $child = $this->child($key);
      if (is_array($weight_s)) {
        $child->setWeights($weight_s);
      }
      elseif (isset($weight_s)) {
        $child->setWeight($weight_s);
      }
    }
  }

  /**
   * @return int|null
   */
  function getWeight() {
    return $this->weight;
  }

  /**
   * @return bool
   *   TRUE, if there are plugins registered at this tree node.
   */
  public function isPluginNode() {
    return !empty($this->plugin) || !empty($this->routePlugins);
  }

  /**
   * Creates a child node, without inserting it.
   *
   * @param string $key
   * @param bool|null $isLeaf
   *
   * @return static
   */
  protected function createChildNode($key, $isLeaf) {
    return new TreeNode($this->pluginType, $isLeaf);
  }

  /**
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  public function offset() {
    return new TreeOffset($this);
  }

  /**
   * @param \crumbs_MultiPlugin $plugin
   *
   * @return $this
   * @throws \Exception
   */
  public function setMultiPlugin(\crumbs_MultiPlugin $plugin) {
    if (TRUE === $this->isLeaf(NULL)) {
      throw new \Exception('Cannot register a multi plugin on a leaf node.');
    }
    $this->validateMultiPlugin($plugin);
    $this->plugin = $plugin;
    $this->processMultiPlugin($plugin);
    $this->setIsLeaf(FALSE);
    return $this;
  }

  /**
   * @param string $route
   * @param \crumbs_MultiPlugin $plugin
   *
   * @return $this
   * @throws \Exception
   */
  public function setRouteMultiPlugin($route, \crumbs_MultiPlugin $plugin) {
    if (TRUE === $this->isLeaf(NULL)) {
      throw new \Exception('Cannot register a multi plugin on a leaf node.');
    }
    $this->validateMultiPlugin($plugin);
    $this->routePlugins[$route] = $plugin;
    $this->processMultiPlugin($plugin);
    $this->setIsLeaf(FALSE);
    return $this;
  }

  /**
   * @param \crumbs_MultiPlugin $plugin
   *
   * @throws \Exception
   */
  protected function processMultiPlugin(\crumbs_MultiPlugin $plugin) {
    $api = new DescribeMultiPluginArg($this);
    $result = $plugin->describe($api);
    if (is_array($result)) {
      foreach ($result as $key => $description) {
        $this->child($key)->describe($description);
      }
    }
  }

  /**
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return $this
   * @throws \Exception
   */
  public function setMonoPlugin(\crumbs_MonoPlugin $plugin) {
    if (FALSE === $this->isLeaf(NULL)) {
      throw new \Exception('Cannot register a mono plugin on a non-leaf node.');
    }
    $this->validateMonoPlugin($plugin);
    $this->plugin = $plugin;
    $this->setIsLeaf(TRUE);
    return $this;
  }

  /**
   * @param string $route
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return $this
   * @throws \Exception
   */
  public function setRouteMonoPlugin($route, \crumbs_MonoPlugin $plugin) {
    if (FALSE === $this->isLeaf(NULL)) {
      throw new \Exception('Cannot register a mono plugin on a non-leaf node.');
    }
    $this->validateMonoPlugin($plugin);
    $this->routePlugins[$route] = $plugin;
    return $this;
  }

  /**
   * @param \crumbs_EntityPlugin $entity_plugin
   * @param string[]|null $types
   *
   * @return $this
   * @throws \Exception
   */
  public function setEntityPlugin(\crumbs_EntityPlugin $entity_plugin, $types = NULL) {
    if (TRUE === $this->isLeaf(NULL)) {
      throw new \Exception('Cannot register an entity plugin on a leaf node.');
    }
    $this->entityPluginAggregate = new EntityPluginAggregate($entity_plugin, $types);
    return $this;
  }

  /**
   * Set default status for this subtree.
   *
   * @return $this
   */
  public function disabledByDefault() {
    $this->status = FALSE;
    return $this;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  public function describe($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * @param string $rawDescription
   * @param string[] $args
   *
   * @return $this
   */
  public function translateDescription($rawDescription, $args) {
    $this->rawDescription = $rawDescription;
    $this->descriptionArgs = $args;
    return $this;
  }

  /**
   * @return bool|null
   */
  public function getDefaultStatus() {
    return $this->status;
  }

  /**
   * @return null|string
   */
  public function getDescription() {
    if (isset($this->description)) {
      return $this->description;
    }
    elseif (isset($this->rawDescription)) {
      return t($this->rawDescription, $this->descriptionArgs);
    }
    else {
      return NULL;
    }
  }

  /**
   * @return \crumbs_PluginInterface|null
   */
  public function getPlugin() {
    return $this->plugin;
  }

  /**
   * @return \crumbs_PluginInterface[]
   */
  public function getRoutePlugins() {
    return $this->routePlugins;
  }

  /**
   * @param string $route
   *
   * @return \crumbs_PluginInterface|null
   */
  public function routeGetPlugin($route) {
    return isset($this->routePlugins[$route])
      ? $this->routePlugins[$route]
      : $this->plugin;
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   *
   * @throws \Exception
   */
  function validatePlugin(\crumbs_PluginInterface $plugin) {
    if (!$this->pluginType->validatePlugin($plugin)) {
      if ('object' === $type = gettype($plugin)) {
        $class = get_class($plugin);
        throw new \Exception("Wrong plugin type: $class.");
      }
      else {
        throw new \Exception("Wrong plugin type: $type.");
      }
    }
  }

  /**
   * @param \crumbs_MonoPlugin $plugin
   *
   * @throws \Exception
   */
  function validateMonoPlugin(\crumbs_MonoPlugin $plugin) {
    if (!$this->pluginType->validateMonoPlugin($plugin)) {
      if ('object' === $type = gettype($plugin)) {
        $class = get_class($plugin);
        throw new \Exception("Wrong plugin type: $class.");
      }
      else {
        throw new \Exception("Wrong plugin type: $type.");
      }
    }
  }

  /**
   * @param \crumbs_MultiPlugin $plugin
   *
   * @throws \Exception
   */
  function validateMultiPlugin(\crumbs_MultiPlugin $plugin) {
    if (!$this->pluginType->validateMultiPlugin($plugin)) {
      if ('object' === $type = gettype($plugin)) {
        $class = get_class($plugin);
        throw new \Exception("Wrong plugin type: $class.");
      }
      else {
        throw new \Exception("Wrong plugin type: $type.");
      }
    }
  }

  /**
   * @return bool
   */
  function isEmpty() {
    return 1
      && empty($this->plugin)
      && empty($this->routePlugins)
      && parent::isEmpty();
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface
   */
  function getPluginType() {
    return $this->pluginType;
  }
}
