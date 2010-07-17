<?php
// $Id$


function crumbs_admin_form() {
  $form = array();
  
  $text = <<<EOT
    <p>Reorder and enable/disable crumbs rules.</p>
    <p>The title coming after the first space in each row can be ignored, it has no effect. What matters is the key before the first space.</p>
    <p>Hint: Copy+paste to edit this text to your favourite text editor, or to import, export and backup. Text editor beats tabledrag, don't you think?</p>
    <p>If a newly installed module introduces new crumbs rules, these rules will find themselves in the "inherit" section at first. Priority and enabled/disabled status of rules in the "inherit" section is inherited from matching wildcard rules in the "enabled" or "disabled" section. The '*' wildcard rule counts a enabled, if it is in the inherit section itself.</p>
EOT;
  $form['instructions'] = array(
    '#value' => t($text),
  );
  
  $form['settings'] = array(
    '#type' => 'textarea',
    '#title' => 'Order of rules.',
    '#description' => 'Each row is a rule',
    '#rows' => 24,
  );
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Save',
  );
  
  $form['settings']['#default_value'] = _crumbs_get_default_text();
  
  return $form;
}


function _crumbs_get_default_text() {
  
  $x = _crumbs_load_plugin_data();
  $lines = array(
    'inherit' => _crumbs_get_available_rule_lines($x),
    'enabled' => array(),
    'disabled' => array(),
  );
    
  
  foreach ($x->order as $order_key => $enabled) {
    if (isset($lines['inherit'][$order_key])) {
      $lines[$enabled ? 'enabled' : 'disabled'][$order_key] = $lines['inherit'][$order_key];
      unset($lines['inherit'][$order_key]);
    }
  }
  
  return "\n\n"
    . implode("\n", $lines['enabled'])
    . "\n\n---- disabled ----\n" . implode("\n", $lines['disabled'])
    . "\n\n---- inherit ----\n" . implode("\n", $lines['inherit'])
    . "\n\n"
  ;
}


function _crumbs_get_available_rule_lines($x) {
  
  $lines = array();
  
  foreach ($x->rules as $rule_key) {
    $lines[$rule_key] = $rule_key;
  }
  foreach ($x->wildcard_rules as $order_key => $rules) {
    $lines[$order_key] = "$order_key (wildcard)";
  }
  foreach ($x->titles as $rule_key => $title) {
    $lines[$rule_key] = "$rule_key ($title)";
  }
  
  return $lines;
}


function crumbs_admin_form_submit($form, &$form_state) {
  
  $x = _crumbs_load_plugin_data();
  $available_lines = _crumbs_get_available_rule_lines($x);
  
  $m = array();
  $text = $form_state['values']['settings'];
  $lines = explode("\n", $text);
  $order = array();
  $enabled = TRUE;
  foreach ($lines as $line) {
    $line = trim($line);
    list($key, $title) = explode(' ', $line, 2);
    if (isset($available_lines[$key])) {
      $order[$key] = $enabled;
    } else if (preg_match('/^-/', $line)) {
      if ($enabled) {
        $enabled = FALSE;
      } else {
        break;
      }
    }
  }
  variable_set('crumbs', $order);
}


