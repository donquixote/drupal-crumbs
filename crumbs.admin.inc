<?php


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
  
  $settings = _crumbs_get_settings();
  $objects = _crumbs_get_objects();
  $lines_active = array();
  $lines_inactive = array();
  $lines_undefined = array();
  foreach ($objects as $key => $object) {
    $line = $key;
    if (method_exists($object, 'getRuleTitle')) {
      $rule_title = str_replace("\n", ' ', check_plain($object->getRuleTitle()));
      if ($rule_title) {
        $line .= " ($rule_title)";
      }
    }
    if (!isset($settings[$key])) {
      $lines_undefined[] = $line;
    } else if ($settings[$key]) {
      $lines_active[] = $line;
    } else {
      $lines_inactive[] = $line;
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
  $settings = array();
  $enabled = TRUE;
  foreach ($lines as $line) {
    $line = trim($line);
    if (!preg_match('/^[\w\d\.\_][\w\d\.\-\_]*/', $line, $m)) {
      $enabled = FALSE;
      continue;
    }
    $settings[$m[0]] = $enabled;
  }
  variable_set('crumbs', $settings);
}


