<?php

class crumbs_Admin_ElementObject_WeightsDropdowns extends crumbs_Admin_ElementObject_WeightsAbstract {

  /**
   * Callback for $element['#value_callback']
   *
   * @param array $element
   * @param array|bool $input
   * @param array $form_state
   * @return array
   */
  function value_callback(&$element, $input = FALSE, $form_state = array()) {
    if ($input === FALSE) {
      return isset($element['#default_value']) ? $element['#default_value'] : array();
    }

    $weights = array();
    foreach ($input as $row_key => $row_values) {
      if (substr($row_key, 0, 6) === 'rules.') {
        $key = substr($row_key, 6);
        switch ($row_values['weight']) {
          case 'disabled':
            $weights[$key] = FALSE;
            break;
          case 'default':
            break;
          default:
            $weights[$key] = (int)$row_values['weight'];
            break;
        }
      }
    }
    return $weights;
  }

  /**
   * Callback for $element['#process']
   * Create one textfield element per rule.
   */
  function process($element, $form_state) {

    /**
     * @var crumbs_Container_CachedLazyPluginInfo $info
     */
    $info = $element['#crumbs_plugin_info'];
    $default_weights = $info->defaultWeights;
    $available_keys_meta = $info->availableKeysMeta;
    $weight_keeper = $info->weightKeeper;

    $max = 0;
    if (is_array($element['#value']) && count($element['#value'])) {
      $max = max($element['#value']);
    }

    $opts = $this->getOptions($max + min(count($available_keys_meta), 20));

    // Set up tabledrag rows
    foreach ($available_keys_meta as $key => $meta) {
      $child = array(
        '#title' => $key,
        'weight' => array(
          '#type' => 'select',
          '#size' => 1,
          '#multiple' => FALSE,
          '#required' => TRUE,
          '#default_value' => 'default',
          '#options' => $opts,
          '#class' => array('crumbs-weight-element'),
        ),
        '#section_key' => 'inherit',
        '#crumbs_rule_info' => $meta,
      );

      $parent = preg_replace('/[^.]+(\.\*)?$/', '*', $key);
      $inheritedValue = $weight_keeper->valueAtKey($parent);
      $child['weight']['#options']['default'] = t('Inherit (!value)', array('!value' => $inheritedValue === FALSE ? 'Disabled' : $inheritedValue));

      $element["rules.$key"] = $child;
    }

    foreach ($default_weights as $key => $value) {
      if (FALSE === $value) {
        $element["rules.$key"]['weight']['#options']['default'] = t('Disabled by default');
        unset($element["rules.$key"]['weight']['#options']['disabled']);
      }
      else {
        $value = (int)$value;
        unset($element["rules.$key"]['weight']['#options']['default']);
        $element["rules.$key"]['weight']['#default_value'] = $value;
        if (!isset($element["rules.$key"]['weight']['#options'][$value])) {
          $element["rules.$key"]['weight']['#options'][$value] = $value;
        }
      }
    }

    if (is_array($element['#value'])) {
      foreach ($element['#value'] as $key => $value) {
        if (isset($element["rules.$key"])) {
          $child = &$element["rules.$key"];
          if (FALSE === $value) {
            $child['weight']['#default_value'] = isset($child['weight']['#options']['disabled']) ? 'disabled' : 'default';
          }
          elseif (is_numeric($value)) {
            $child['weight']['#default_value'] = $value;
          }
        }
      }
    }

    // * can't inherit
    unset($element['rules.*']['weight']['#options']['default']);

    $crumbspath = drupal_get_path('module', 'crumbs');

    if (module_exists('token')) {
      $element['#attached']['css'][] = drupal_get_path('module', 'token') . '/jquery.treeTable.css';
      $element['#attached']['js'][]  = drupal_get_path('module', 'token') . '/jquery.treeTable.js';
    }
    else {
      $element['#attached']['css'][] = $crumbspath . '/css/jquery.treeTable.css';
      $element['#attached']['js'][]  = $crumbspath . '/js/jquery.treeTable.js';
    }

    // Attach all js files.
    foreach (array('model', 'widget', 'dropdowns') as $subfolder) {
      foreach (scandir($crumbspath . '/js/' . $subfolder) as $candidate) {
        if ('.' === $candidate{0}) {
          continue;
        }
        $element['#attached']['js'][] = $crumbspath . '/js/' . $subfolder . '/' . $candidate;
      }
    }

    $element['#attached']['css'][] = $crumbspath . '/css/crumbs.admin.dropdowns.css';
    $element['#attached']['js'][]  = $crumbspath . '/js/crumbs.admin.dropdowns.js';

    return $element;
  }

  /**
   * @param int $sup
   * @return string[]
   */
  protected function getOptions($sup) {
    $opts = array(
      'default' => t('Inherit'),
      'disabled' => t('Disabled'),
    );

    for ($i = 1; $i < $sup; $i++) {
      $opts[$i] = t('Enabled') . ': ' . $i;
    }

    return $opts;
  }
}
