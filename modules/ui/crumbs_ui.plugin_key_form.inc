<?php
use Drupal\crumbs_ui\PluginKey\PluginKeyInterface;

/**
 * @param array $form
 * @param array $form_state
 * @param \Drupal\crumbs_ui\PluginKey\PluginKeyInterface $plugin_key
 *
 * @return array
 */
function _crumbs_ui_plugin_key_form($form, $form_state, PluginKeyInterface $plugin_key) {
  $form = _crumbs_ui_plugin_key_elements($plugin_key);
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save configuration'));
  return $form;
}

/**
 * @param \Drupal\crumbs_ui\PluginKey\PluginKeyInterface $plugin_key
 *
 * @return array
 */
function _crumbs_ui_plugin_key_elements(PluginKeyInterface $plugin_key) {
  $placeholders = array(
    '!plugin_key' => '<code>' . $plugin_key->__toString() . '</code>',
  );
  $elements = array();
  $elements['intro'] = array(
    '#type' => 'fieldset',
    '#title' => t('Description'),
  );
  $elements['intro']['text']['#markup'] = $plugin_key->getLongDescription();

  $parent_key = $plugin_key->getParentKey();
  if (isset($parent_key)) {

    $elements['parents'] = array(
      '#type' => 'fieldset',
      '#title' => t('Parents'),
    );
    $trail_html = '<li>' . $plugin_key->__toString() . '</li>';
    $trail_key = $plugin_key;
    while ($trail_key = $trail_key->getParentKey()) {
      $trail_html = '<li>' . _crumbs_ui_plugin_key_link($trail_key) . '</li>' . $trail_html;
    }
    $trail_html = '<ol>' . $trail_html . '</ol>';
    $elements['parents']['trail']['#markup'] = $trail_html;
  }

  $elements['status'] = array(
    '#type' => 'fieldset',
    '#title' => t('Status'),
    '#description' => $plugin_key->isWildcardKey()
      ? t('Status for !plugin_key and its children, unless overridden:', $placeholders)
      : t('Status for !plugin_key:', $placeholders),
  );
  $elements['status']['status'] = array(
    # '#title' => t('Setting for !plugin_key and its children, unless overridden', $placeholders) . ':',
    '#type' => 'radios',
    '#pre_render' => array('_crumbs_ui_radios_describe'),
    '#options' => _crumbs_ui_plugin_key_widget_options($plugin_key),
    '#default_value' => _crumbs_ui_plugin_key_widget_default_value($plugin_key),
  );

  if ($plugin_key->isWildcardKey()) {
    $children_links = '';
    foreach ($plugin_key->getChildren() as $child_key) {
      $children_links .= '<li>' . _crumbs_ui_plugin_key_link($child_key) . '</li>';
    }
    $children_links .= '<li>' . t('Any children that may be added in the future.') . '</li>';
    $children_links = '<ul>' . $children_links . '</ul>';
    $children_links = '<p>' . t('The plugin key has a number of child plugin keys.') . '<br/>'
      . t('These may either inherit the status and weight of !plugin_key, or override it.', $placeholders) . '</p>'
      . $children_links;
    $elements['children'] = array(
      '#type' => 'fieldset',
      '#title' => t('Child plugin keys'),
    );
    $elements['children']['children_links'] = array(
      '#markup' => $children_links,
    );
  }
  return $elements;
}

/**
 * @param \Drupal\crumbs_ui\PluginKey\PluginKeyInterface $plugin_key
 *
 * @return string
 */
function _crumbs_ui_plugin_key_widget_default_value(PluginKeyInterface $plugin_key) {
  if (!$plugin_key->hasExplicitValue()) {
    return 'inherit';
  }
  elseif ($plugin_key->isEnabled()) {
    return 'enabled';
  }
  else {
    return 'disabled';
  }
}

/**
 * @param \Drupal\crumbs_ui\PluginKey\PluginKeyInterface $plugin_key
 *
 * @return string[]
 *   Array of options for form radios.
 */
function _crumbs_ui_plugin_key_widget_options(PluginKeyInterface $plugin_key) {

  $parent_key = $plugin_key->getParentKey();

  if (NULL === $parent_key) {
    // This is the root wildcard, '*'.
    $options['disabled'] = t('Disabled') . '<br/>'
      . t('Unless overridden, all plugins will be <em>disabled</em> by default, unless overridden.');
    $options['enabled'] = t('Enabled') . '<br/>'
      . t('Unless overridden, all plugins will be <em>enabled</em> by default, inheriting the weight from the * plugin key.') . '<br/>'
      . t('This will give it a distinct weight, so it can be individually prioritized.');
    return $options;
  }

  $placeholders = array();
  $parent_key = $plugin_key->getParentKey();
  $placeholders['!parent_key'] = _crumbs_ui_plugin_key_link($parent_key);
  $placeholders['!parent_status'] = $parent_key->isEnabled()
    ? t('enabled') . ', ' . t('weight = !weight', array('!weight' => $parent_key->getWeight()))
    : t('disabled');

  if ($plugin_key->isDisabledByDefault()) {
    $options['disabled'] = t('Leave this plugin key disabled.');
    $options['enabled'] = t('Explicitly *enable* this plugin key.')
      . t('This will give it a distinct weight, so it can be individually prioritized.');
  }
  else {
    $options['inherit'] = t('Inherit') . '<br/>'
      . t('Inherit the status and weight from !parent_key, currently !parent_status.', $placeholders) . '<br/>';
    $options['disabled'] = t('Disabled') . '<br/>'
      . t('Override the status and weight of !parent_key, and explicitly <em>disable</em> this plugin key.', $placeholders);
    $options['enabled'] = t('Enabled') . '<br/>'
      . t('Override the status and weight of !parent_key, and explicitly <em>enable</em> this plugin key.', $placeholders) . '<br/>'
      . t('This will give it a distinct weight, so it can be individually prioritized.');
  }

  return $options;
}
