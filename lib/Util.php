<?php

/**
 * Static methods that are stateless and do not depend on anything.
 */
class crumbs_Util {

  /**
   * Clean tokens so they are URL friendly.
   * Taken directly from pathauto_clean_token_values()
   *
   * @param $replacements
   *   An array of token replacements that need to be "cleaned" for use in the URL.
   * @param $data
   *   An array of objects used to generate the replacements.
   * @param $options
   *   An array of options used to generate the replacements.
   */
  static function cleanTokenValues(&$replacements, $data = array(), $options = array()) {

    foreach ($replacements as $token => &$value) {
      // Only clean non-path tokens.
      if (!preg_match('/(path|alias|url|url-brief)\]$/', $token)) {
        // We use a simple regex instead of pathauto_cleanstring().
        $value = preg_replace('#[^a-z0-9/]+#', '-', strtolower($value));
      }
    }
  }

  /**
   * This function has exactly one possible input value for
   * each possible return value, except the return value FALSE.
   *
   * @param string $route
   *   The router path can contain any character, but will typically
   *   have a format like "node/%/edit".
   *
   * @return string or FALSE
   *   A string that can be used as a method suffix,
   *   or FALSE, where that is not possible.
   *   The route "node/%/edit" will resolve as "node_x_edit".
   */
  static function buildMethodSuffix($route) {
    $method_suffix = strtolower($route);
    $method_suffix = preg_replace('#[^a-z0-9\%]#', '_', $method_suffix);
    $method_suffix = strtr($method_suffix, array('%' => 'x'));
    $reverse = self::routeFromMethodSuffix($method_suffix);
    if ($reverse === $route) {
      return $method_suffix;
    }
    return FALSE;
  }

  /**
   * @param string $method_suffix
   * @return string
   */
  static function routeFromMethodSuffix($method_suffix) {
    $route = strtr($method_suffix, array('_' => '/'));
    $route = preg_replace(array('#/x/#', '#/x$#'), array('/%/', '/%'), $route);
    // we need to do this two time to catch things like "/x/x/x/x".
    $route = strtr($route, array('/x/' => '/%/'));
    return $route;
  }

  /**
   * Measures time between two runs and displays it in a human-readable format.
   *
   * @param $label
   *   String used to identify the measured time.
   * @return null|string
   *   A string with the measurement, or NULL if the function was run for the
   *   first time.
   */
  static function benchmark($label = NULL) {
    static $previous_time;

    $current_time = microtime(TRUE);
    if (isset($previous_time) && isset($label)) {
      $str = 'Duration: ' . number_format(1000 * ($current_time - $previous_time), 3) . ' ms for '. $label;
    }
    $previous_time = $current_time;
    return isset($str) ? $str : NULL;
  }
}
