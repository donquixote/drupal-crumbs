<?php

/**
 * The form displayed on admin/structure/crumbs(/weights)
 */
function crumbs_admin_weights_form($form, $form_state, $type = 'tabledrag') {

  $form = array();

  /**
   * @var crumbs_Container_CachedLazyPluginInfo $info
   */
  $info = crumbs('pluginInfo');

  // Re-discover plugins, when the admin visits the weights configuration form.
  $info->flushCaches();

  $form['crumbs_weights'] = array(
    '#title' => t('Weights'),
    // Fetching the default value is not automated by system_settings_form().
    '#default_value' => $info->userWeights,
    '#crumbs_plugin_info' => $info,
  );

  switch ($type) {
    case 'textual':
      // You need to enable crumbs_labs to get this.
      $form['crumbs_weights']['#type'] = 'crumbs_weights_textual';
      break;
    case 'expansible':
      // You need to enable crumbs_labs to get this.
      $form['crumbs_weights']['#type'] = 'crumbs_weights_expansible';
      break;
    case 'tabledrag':
    default:
      $form['crumbs_weights']['#type'] = 'crumbs_weights_tabledrag';
  }

  $form = system_settings_form($form);
  $form['#submit'][] = '_crumbs_admin_flush_cache';
  return $form;
}

/**
 * The form displayed on admin/structure/crumbs/display
 */
function crumbs_admin_display_form() {
  $form = array();

  // Home link settings
  $form['home_link_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Home link settings'),
  );
  $form['home_link_settings']['crumbs_show_front_page'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show the home page link (recommended).'),
    '#default_value' => variable_get('crumbs_show_front_page', TRUE),
  );
  $form['home_link_settings']['crumbs_home_link_title'] = array(
    '#type' => 'textfield',
    '#title' => t('Home link title'),
    '#default_value' => variable_get('crumbs_home_link_title', 'Home'),
    '#description' => t('Title of the link that points to the front page.'),
    '#size' => 30,
  );

  // Current page settings
  $form['current_page_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Current page settings'),
  );
  $form['current_page_settings']['crumbs_show_current_page'] = array(
    '#type' => 'radios',
    '#title' => t('Show the current page at the end of the breadcrumb trail?'),
    '#options' => array(
      // @todo Smarter option values
      FALSE => t('Hide.'),
      CRUMBS_TRAILING_SEPARATOR => t('Hide, but end the trail with a separator.'),
      CRUMBS_SHOW_CURRENT_PAGE => t('Show, as plain text.'),
      CRUMBS_SHOW_CURRENT_PAGE_SPAN => t('Show, wrapped in !tags tags.', array(
        '!tags' =>  '<code>&lt;span class="crumbs-current-page"&gt;</code>',
      )),
      CRUMBS_SHOW_CURRENT_PAGE_LINK => t('Show, as a link.'),
    ),
    '#default_value' => variable_get('crumbs_show_current_page', FALSE),
  );

  // Visibility settings
  $form['visibility_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Breadcrumb visibility settings'),
  );
  $home = t('Home');
  $current = t('Current page');
  $intermediate = t('Intermediate page');
  $form['visibility_settings']['crumbs_minimum_trail_items'] = array(
    '#type' => 'radios',
    '#title' => t('Shortest visible breadcrumb'),
    '#description' => t('If the trail has fewer items than specified here, the breadcrumb will be hidden.'),
    '#default_value' => variable_get('crumbs_minimum_trail_items', 2),
    '#options' => array(
      1 => "($home)",
      2 => "(<a href='#'>$home</a>) &raquo; ($current)",
      3 => "(<a href='#'>$home</a>) &raquo; <a href='#'>$intermediate</a> &raquo; ($current)",
    ),
  );

  // Separator settings
  $form['separator_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Separator settings'),
  );
  $form['separator_settings']['crumbs_separator_span'] = array(
    '#type' => 'checkbox',
    '#title' => t('Wrap the separator in !tags tags:', array(
        '!tags' => '<code>&lt;span class="crumbs-separator"&gt;</code>',
      )),
    '#default_value' => variable_get('crumbs_separator_span', FALSE),
  );
  $separator_notes = '';
  foreach (array(
    'Be careful, this is a <em>free HTML</em> field. The output will be printed as-is.',
    'Special characters should be specified as htmlentities, e.g. "&amp;raquo;".',
    'Spaces should be added around the separator symbol.',
    'The setting will only work in themes where the Crumbs implementation of theme_breadcrumb() is used.',
  ) as $note) {
    $separator_notes .= '<li>' . str_replace("\n", '<br/>', t($note)) . '</li>';
  }
  $separator_notes = '<ul>' . $separator_notes . '</ul>';
  $separator_desc = '<p>' . t('A custom separator symbol, such as " &amp;raquo; " ( &raquo; ) or " &amp;gt; " (&gt).') . '</p>';
  $form['separator_settings']['crumbs_separator'] = array(
    '#type' => 'textfield',
    '#title' => t('Custom separator HTML'),
    '#description' => $separator_desc,
    '#default_value' => variable_get('crumbs_separator', ' &raquo; '),
  );
  $form['separator_settings']['notes'] = array(
    '#type' => 'markup',
    '#markup' => '<p>' . t('Notes:') . '</p>' . $separator_notes,
  );

  // Theme override settings
  $theme_override_options = array();
  $other_themes = array();
  foreach (list_themes() as $theme_name => $theme_obj) {
    if ('1' !== '' . $theme_obj->status) {
      continue;
    }
    $f = $theme_name . '_breadcrumb';
    if (function_exists($f)) {
      $theme_override_options[$theme_name] = l($theme_obj->info['name'], 'admin/appearance/settings/' . $theme_name);
    }
    else {
      $other_themes[$theme_name] = l($theme_obj->info['name'], 'admin/appearance/settings/' . $theme_name);
    }
  }
  if (!empty($other_themes)) {
    $theme_override_options['(other)'] = t('Themes that don\'t implement theme_breadcrumb(): !other_themes.', array(
      '!other_themes' => implode(', ', $other_themes),
    ));
  }
  else {
    $theme_override_options['(other)'] = t('Themes that don\'t implement theme_breadcrumb().');
  }
  if (!empty($theme_override_options)) {
    $form['theme_override_settings']['crumbs_override_theme_breadcrumb'] = array(
      '#type' => 'checkboxes',
      '#options' => $theme_override_options,
      '#html' => TRUE,
      '#default_value' => variable_get('crumbs_override_theme_breadcrumb', array('(other)')),
      '#title' => t('Override theme implementations'),
    );
    $form['theme_override_settings']['notes'] = array(
      '#type' => 'markup',
      '#markup' =>
        '<p>' . t('Use !crumbs_module_implementation instead of the respective theme implementation.', array(
            '!crumbs_module_implementation' => '<code>crumbs_theme_breadcrumb()</code>',
          )) . '<br/>' .
        t('You need to flush the theme registry for this setting to take effect!') . '</p>',
    );
  }
  if (!empty($form['theme_override_settings'])) {
    $form['theme_override_settings'] += array(
      '#type' => 'fieldset',
      '#title' => t('Theme override settings'),
    );
  }

  $form = system_settings_form($form);
  $form['#submit'][] = '_crumbs_admin_flush_cache';
  return $form;
}
