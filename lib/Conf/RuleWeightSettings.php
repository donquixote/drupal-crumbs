<?php


class crumbs_Conf_RuleWeightSettings {

  protected $weights;

  function __construct() {
    $this->load();
  }

  /**
   * Load weight settings
   */
  function load() {
    $weights = variable_get('crumbs_weights', array(
      'crumbs.home_title' => 0,
    ));
    $this->set($weights);
  }

  /**
   * Set weight settings.
   * This is called internally by load(), and externally when the configuration
   * form is submitted.
   */
  function set($weights) {
    asort($weights);
    if (!isset($weights['*'])) {
      $weights['*'] = count($weights);
    }
    $this->weights = $weights;
  }

  /**
   * Write current weights to the database.
   */
  function save() {
    variable_set('crumbs_weights', $this->weights);
  }

  /**
   * Return the current (raw) weights
   */
  function get() {
    return $this->weights;
  }

  /**
   * Remix the current (raw) weights with the "disabled by default" settings.
   */
  function buildWeights($disabled_keys) {
    $weights = crumbs_get_weights();
    foreach ($disabled_keys as $key => $disabled) {
      if (!isset($weights[$key])) {
        $weights[$key] = FALSE;
      }
    }
    return $weights;
  }
}
