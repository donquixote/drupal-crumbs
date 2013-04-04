<?php

class crumbs_Admin_ElementObject_WeightsTextual extends crumbs_Admin_ElementObject_WeightsAbstract {

  /**
   * Callback for $element['#value_callback']
   */
  function value_callback(&$element, $input = FALSE, $form_state = array()) {

    if (FALSE === $input) {
      return isset($element['#default_value']) ? $element['#default_value'] : array();
    }

    list($plugins, $disabled_keys) = crumbs_get_plugins();
    list($available_keys, $keys_by_plugin) = $this->loadAvailableKeys($plugins);

    $weights = array();
    $weight = 0;
    foreach (explode("\n", $input['text']) as $line) {
      $line = trim($line);
      list($key, $title) = explode(' ', $line, 2) + array(NULL, NULL);
      if (isset($available_keys[$key])) {
        $weights[$key] = $weight;
        ++$weight;
      }
      elseif (preg_match('/^-/', $line)) {
        if ($weight !== FALSE) {
          $weight = FALSE;
        }
        else {
          break;
        }
      }
    }

    return $weights;
  }

  /**
   * Callback for $element['#process']
   * Create a big textarea.
   */
  function process($element, $form_state) {

    $text = $this->getDefaultText($element['#value']);
    $element['text'] = array(
      '#tree' => TRUE,
      '#type' => 'textarea',
      '#rows' => 24,
      '#default_value' => $text,
    );
    return $element;
  }

  /**
   * Get the text for the textarea
   */
  protected function getDefaultText($weights) {

    list($plugins, $disabled_keys) = crumbs_get_plugins();
    list($available_keys, $keys_by_plugin) = $this->loadAvailableKeys($plugins);
    $weights = crumbs('pluginInfo')->userWeights;

    return $this->buildDefaultText($available_keys, $keys_by_plugin, $weights, $disabled_keys);
  }

  /**
   * Build the text for the textarea
   */
  protected function buildDefaultText(array $available_keys, array $keys_by_plugin, array $weights, array $disabled_keys) {

    $key_lengths = array();
    foreach ($available_keys as $key => $title) {
      $key_lengths[] = strlen($key);
    }
    $ideal_length = $this->findIdealLength($key_lengths);

    foreach ($available_keys as $key => $title) {
      $string = $key;
      if (is_string($title)) {
        if (strlen($string) < $ideal_length) {
          $string .= str_repeat(' ', $ideal_length - strlen($string));
        }
        $string .= ' - '. $title;
      }
      $available_keys[$key] = $string;
    }

    $lines = array(
      'inherit' => $available_keys,
      'disabled_by_default' => array(),
      'enabled' => array(),
      'disabled' => array(),
    );

    foreach ($weights as $key => $weight) {
      $section = ($weight === FALSE) ? 'disabled' : 'enabled';
      $string = $key;
      if (isset($available_keys[$key])) {
        $string = $available_keys[$key];
      }
      else if ($key !== '*') {
        // an orphan setting.
        if (strlen($string) < $ideal_length) {
          $string .= str_repeat(' ', $ideal_length - strlen($string));
        }
        $string .= '   (orphan rule)';
      }
      $lines[$section][$key] = $string;
      unset($lines['inherit'][$key]);
    }

    foreach ($disabled_keys as $key => $disabled) {
      if (isset($lines['inherit'][$key])) {
        $lines['disabled_by_default'][$key] = $lines['inherit'][$key];
        unset($lines['inherit'][$key]);
      }
    }

    foreach ($keys_by_plugin as $plugin_key => $keys_for_this_plugin) {
      $lines['inherit'][$plugin_key .':NEWLINE:'] = "";
    }

    ksort($lines['inherit']);
    foreach ($lines['inherit'] as $key => $line) {
      if (isset($prev) && $prev === '' && $line === '') {
        unset($lines['inherit'][$key]);
      }
      $prev = $line;
    }

    return "\n\n"
      . implode("\n", $lines['enabled'])
      . "\n\n\n---- disabled ----\n\n". implode("\n", $lines['disabled'])
      . "\n\n\n---- disabled by default ----\n\n". implode("\n", $lines['disabled_by_default'])
      . "\n\n\n---- inherit ----\n\n". implode("\n", $lines['inherit'])
      . "\n\n"
    ;
  }

  /**
   * This algorithm is copied 1:1 from blockadminlight
   */
  protected function findIdealLength(array $key_lengths, $factor = 30) {
    sort($key_lengths, SORT_NUMERIC);
    $n = count($key_lengths);
    $length = 0;
    $best_length = 0;
    $cost = $n * $factor;
    $best_cost = $cost;
    for ($i=0; $i<$n; ++$i) {
      $increment = $key_lengths[$i] - $length;
      $length = $key_lengths[$i];
      $cost += $i * $increment;
      $cost -= $factor;
      if ($cost < $best_cost) {
        $best_cost = $cost;
        $best_length = $length;
      }
    }
    return $best_length;
  }
}
