<?php


/**
 * Form builder callback
 */
function crumbs_admin_entity_parent_form($form, &$form_state, $entity_type) {

  $info = entity_get_info($entity_type);

  $text = <<<EOT
<p>You may specify a pattern for the parent path for each entity bundle.</p>
EOT;
  $doc = t($text);

  $text = <<<EOT
<p>Whatever you configure here, will be available on the Crumbs settings page as<br/>
crumbs.entityParent.$entity_type.[type]</p>
EOT;
  $doc .= t($text);

  if (!module_exists('token')) {
    $text = <<<EOT
<p>You will get more dynamic possibilities, if you install the !token_module_link module.</p>
EOT;
    $doc .= t($text, array(
      '!token_module_link' => l('Token', 'http://drupal.org/project/token', array('external' => TRUE)),
    ));
  }
  elseif (!module_exists('entity_token')) {
    $text = <<<EOT
<p>You will get more interesting tokens, if you install the entity_token module.<br/>
(included in !entity_module_link module)</p>
EOT;
    $doc .= t($text, array(
      '!entity_module_link' => l('Entity', 'http://drupal.org/project/entity', array('external' => TRUE)),
    ));
  }

  $form = array();
  $form['intro'] = array(
    '#markup' => $doc,
  );

  $form['crumbs_' . $entity_type . '_parent_patterns'] = array(
    '#title' => t('Bundles'),
    '#tree' => TRUE,
  );

  $settings = variable_get('crumbs_' . $entity_type . '_parent_patterns', array());

  foreach ($info['bundles'] as $bundle_key => $bundle) {
    $element = array(
      '#type' => 'textfield',
      '#title' => t($bundle['label']),
      '#description' => t('Crumbs parent path for !plural of type "!bundle_label" (!bundle_key)', array(
        '!plural' => t($info['plural label']),
        '!bundle_label' => t($bundle['label']),
        '!bundle_key' => $bundle_key,
      )),
      // The maximum length is quite arbitrary.
      // Pathauto has 1280, but we think that 512 is enough.
      '#maxlength' => 512,
      '#default_value' => @$settings[$bundle_key],
    );
    if (module_exists('token')) {
      $element['#element_validate'][] = 'token_element_validate';
      $element['#token_types'][] = $info['token type'];
    }
    $form['crumbs_' . $entity_type . '_parent_patterns'][$bundle_key] = $element;
  }

  if (module_exists('token')) {
    $form['token_help'] = array(
      '#title' => t('Replacement patterns'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['token_help']['help'] = array(
      '#theme' => 'token_tree',
      '#token_types' => array($info['token type']),
    );
  }

  return system_settings_form($form);
}
