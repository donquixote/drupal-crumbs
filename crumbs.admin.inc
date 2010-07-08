<?php
// $Id$


function crumbs_admin_form() {
  $form = array();
  
  $text = <<<EOT
    <p>Reorder and enable/disable crumbs rules.</p>
    <p>The title coming after the first space in each row can be ignored, it has no effect. What matters is the key before the first space.</p>
    <p>Hint: Copy+paste to edit this text to your favourite text editor, or to import, export and backup. Text editor beats tabledrag, don't you think?</p>
EOT;
  $form['instructions'] = array(
    '#value' => t($text),
  );
  
  $form['settings'] = array(
    '#type' => 'textarea',
    '#title' => 'Order of rules.',
    '#description' => 'Each row is a rule',
    '#rows' => 20,
  );
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Save',
  );
  
  $order = _crumbs_get_settings();
  $lines = _crumbs_get_available_rule_lines();
  $lines_active = array();
  $lines_inactive = array();
  $lines_inherit = $lines;
  foreach ($order as $order_key => $enabled) {
    if (isset($lines_inherit[$order_key])) {
      if ($enabled) {
        $lines_active[$order_key] = $lines_inherit[$order_key];
      }
      else {
        $lines_inactive[$order_key] = $lines_inherit[$order_key];
      }
      unset($lines_inherit[$order_key]);
    }
  }
  
  $text
    = implode("\n", $lines_active)
    . "\n---- disabled ----\n" . implode("\n", $lines_inactive)
    . "\n---- inherit ----\n" . implode("\n", $lines_inherit)
  ;
  
  $form['settings']['#default_value'] = $text;
  
  return $form;
}


function _crumbs_get_available_rule_lines() {
  $objects = _crumbs_get_objects();
  $lines = array();
  foreach ($objects as $object_key => $object) {
    list($module, $instance_key) = explode('.', $object_key, 2);
    $rule_titles = array();
    if (method_exists($object, 'getRuleTitles')) {
      $rule_titles = $object->getRuleTitles();
    }
    else if (method_exists($object, 'getRuleTitle')) {
      $rule_titles[''] = $object->getRuleTitle();
    }
    else {
      $rule_titles[''] = NULL;
    }
    foreach ($rule_titles as $sub_key => $rule_title) {
      if (strlen($sub_key)) {
        $lines["$object_key:*"] = "$object_key:* (wildcard)";
        $order_key = "$object_key:$sub_key";
      }
      else {
        $order_key = $object_key;
      }
      if (strlen($instance_key)) {
        $lines["$module.*"] = "$module.* (wildcard)";
      }
      $line = $order_key;
      if (strlen($rule_title)) {
        $rule_title = str_replace("\n", ' ', check_plain($rule_title));
        $line .= " ($rule_title)";
      }
      $lines[$order_key] = $line.'';
    }
  }
  return $lines;
}


function crumbs_admin_form_submit($form, &$form_state) {
  $m = array();
  $text = $form_state['values']['settings'];
  $lines = explode("\n", $text);
  $order = array();
  $enabled = TRUE;
  foreach ($lines as $line) {
    $line = trim($line);
    if (!preg_match('/^[\w\d\.\_][\w\d\.\-\_\:\*]*/', $line, $m)) {
      if ($enabled) {
        $enabled = FALSE;
        continue;
      } else {
        break;
      }
    }
    $order[$m[0]] = $enabled;
  }
  variable_set('crumbs', $order);
}


