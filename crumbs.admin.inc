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
  $objects = _crumbs_get_objects();
  $lines_active = $order;
  $lines_inactive = $order;
  $lines_undefined = array();
  foreach ($objects as $object_key => $object) {
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
      $order_key = strlen($sub_key) ? "$object_key:$sub_key" : $object_key;
      $line = $order_key;
      if (strlen($rule_title)) {
        $rule_title = str_replace("\n", ' ', check_plain($rule_title));
        $line .= " ($rule_title)";
      }
      if (!isset($order[$order_key])) {
        // make sure the value is a string, so we can use is_string() later.
        $lines_undefined[$order_key] = $line.'';
      } else if ($order[$order_key]) {
        $lines_active[$order_key] = $line.'';
      } else {
        $lines_inactive[$order_key] = $line.'';
      }
    }
  }
  foreach ($lines_active as $order_key => $v) {
    if ($v === TRUE || $v === FALSE) {
      unset($lines_active[$order_key]);
    }
  }
  foreach ($lines_inactive as $order_key => $v) {
    if ($v === TRUE || $v === FALSE) {
      unset($lines_inactive[$order_key]);
    }
  }
  
  $text =
    implode("\n", $lines_active) .
    "\n---- undefined ----\n" . implode("\n", $lines_undefined) .
    "\n---- disabled ----\n" . implode("\n", $lines_inactive)
  ;
  
  $form['settings']['#default_value'] = $text;
  
  return $form;
}


function crumbs_admin_form_submit($form, &$form_state) {
  $m = array();
  $text = $form_state['values']['settings'];
  $lines = explode("\n", $text);
  $order = array();
  $enabled = TRUE;
  foreach ($lines as $line) {
    $line = trim($line);
    if (!preg_match('/^[\w\d\.\_][\w\d\.\-\_\:]*/', $line, $m)) {
      $enabled = FALSE;
      continue;
    }
    $order[$m[0]] = $enabled;
  }
  variable_set('crumbs', $order);
}


