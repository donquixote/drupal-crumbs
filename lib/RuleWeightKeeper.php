<?php

/**
 * Can determine a weight for a rule key based on wildcard weights.
 *
 * E.g. if the weight settings are
 *   * = 0
 *   crumbs.* = 4
 *   crumbs.nodeParent.* = 5
 * and then we are looking for a weight for
 *   crumbs.nodeParent.page
 * then the weight keeper will return 5, because crumbs.nodeParent.* is the best
 * matching wildcard.
 */
class crumbs_RuleWeightKeeper {

  protected $ruleWeights;
  protected $prefixedKeepers = array();
  protected $prefixSorted = array();
  protected $soloSorted = array();

  /**
   * @param array $rule_weights
   *   Weights with wildcards, as saved in the configuration form.
   */
  function __construct(array $rule_weights) {
    asort($rule_weights);
    $this->ruleWeights = $rule_weights;
  }

  /**
   * Get a "child" weight keeper with a prefix.
   * E.g. if the config contains a weight setting "crumbs.nodeParent.* = 5"
   * then in the child keeper this will be just "nodeParent.* = 5".
   *
   * @param string $prefix
   *   The prefix.
   *
   * @return crumbs_RuleWeightKeeper
   *   The prefixed weight keeper.
   */
  function prefixedWeightKeeper($prefix) {
    if (!isset($this->prefixedKeepers[$prefix])) {
      $this->prefixedKeepers[$prefix] = $this->_buildPrefixedWeightKeeper($prefix);
    }
    return $this->prefixedKeepers[$prefix];
  }

  /**
   * Helper: Actually build the prefixed keeper.
   *
   * @param string $prefix
   *   Prefix, as above.
   *
   * @return crumbs_RuleWeightKeeper
   *   The prefixed weight keeper.
   */
  protected function _buildPrefixedWeightKeeper($prefix) {
    $weights = array();
    $k = strlen($prefix);
    $weights[''] = $weights['*'] = $this->_findWildcardWeight($prefix);
    if (isset($this->ruleWeights[$prefix])) {
      $weights[''] = $this->ruleWeights[$prefix];
    }
    if (isset($this->ruleWeights[$prefix .'.*'])) {
      $weights['*'] = $this->ruleWeights[$prefix .'.*'];
    }
    foreach ($this->ruleWeights as $key => $weight) {
      if (strlen($key) > $k && substr($key, 0, $k+1) === ($prefix .'.')) {
        $weights[substr($key, $k+1)] = $weight;
      }
    }
    return new crumbs_RuleWeightKeeper($weights);
  }

  /**
   * Get the smallest weight in range.
   *
   * @return int
   *   The smallest weight..
   */
  function getSmallestWeight() {
    foreach ($this->ruleWeights as $weight) {
      if ($weight !== FALSE) {
        return $weight;
      }
    }
    return FALSE;
  }

  /**
   * Determine the weight for the rule specified by the key.
   *
   * @param string $key
   *   Key that we are looking for.
   *
   * @return int
   *   The weight for this key.
   */
  function findWeight($key = NULL) {
    if (!isset($key)) {
      // Find the top-level weight.
      return $this->ruleWeights[''];
    }
    if (isset($this->ruleWeights[$key])) {
      // Look for explicit weight setting.
      return $this->ruleWeights[$key];
    }
    // Try wildcards.
    return $this->_findWildcardWeight($key);
  }

  /**
   * Helper: Resolve wildcards..
   *
   * @param string $key
   *   Key that we are looking for.
   *
   * @return int
   *   The weight for this key.
   */
  protected function _findWildcardWeight($key) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    $weight = $this->ruleWeights['*'];
    while (!empty($fragments)) {
      if (isset($this->ruleWeights[$partial_key .'.*'])) {
        $weight = $this->ruleWeights[$partial_key .'.*'];
      }
      $partial_key .= '.'. array_shift($fragments);
    }
    return $weight;
  }
}
