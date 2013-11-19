<?php

/**
 * Default theme implementation for theme('crumbs_weights_dropdowns')
 *
 * @param array &$vars
 *
 * @return string
 *   Rendered HTML for the dropdowns widget.
 */
function theme_crumbs_weights_dropdowns(&$vars) {
  $element = $vars['element'];

  $rows = array();
  foreach (element_children($element) as $key) {
    $elt = $element[$key];
    $cells = array();
    $cells[] = $elt['#title'];
    $cells[] = render($elt);

    /**
     * @var crumbs_Container_MultiWildcardDataOffset $meta
     */
    $meta = $elt['#crumbs_rule_info'];
    $methods = array();
    $routes = array();
    if (is_array($meta->routeMethods)) {
      foreach ($meta->routeMethods as $method => $method_routes) {
        foreach ($method_routes as $route => $method_with_suffix) {
          $methods[] = $method . '()';
          $routes[] = $route;
        }
      }
    }
    if (is_array($meta->basicMethods)) {
      foreach ($meta->basicMethods as $method) {
        $methods[] = $method . '()';
        $routes[] = '-';
      }
    }

    $cells[] = '<code class="crumbs-admin-meta">' . implode('<br/>', $methods) . '</code>';
    $cells[] = '<code class="crumbs-admin-meta">' . implode('<br/>', $routes) . '</code>';

    if ($descriptions = $meta->descriptions) {
      $cells[] = '<div class="crumbs-admin-meta">' . implode('<br/>', $descriptions) . '</div>';
    }
    else {
      $cells[] = '';
    }

    $row = array('data' => $cells);
    $row['id'] = 'crumbs-rule-' . md5($meta->getKey());
    $parent_key = $meta->getParentKey();
    if (NULL === $parent_key) {
      # $row['class'][] = 'crumbs-rule-depth-0';
    }
    elseif ('*' === $parent_key) {
      # $row['class'][] = 'crumbs-rule-depth-1';
    }
    else {
      $row['class'][] = 'child-of-crumbs-rule-' . md5($parent_key);
      # $row['class'][] = 'crumbs-rule-depth-' . count(explode('.', $parent_key));
    }
    $row['no_striping'] = TRUE;

    $rows[$key] = $row;
  }

  ksort($rows);

  return theme('table', array(
    'rows' => $rows,
    'header' => array(
      t('Plugin name'),
      t('Weight') . ' / ' . t('Status'),
      t('Method'),
      t('Route'),
      t('Description'),
    ),
    'attributes' => array('id' => 'crumbs_weights_dropdowns'),
  ));
}
