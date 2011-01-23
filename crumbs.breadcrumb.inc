<?php


/**
 * Default theme implementation for theme('crumbs_breadcrumb').
 * The default implementation uses the theme's implementation of
 * theme('breadcrumb').
 * Themes that are crumbs-aware can override this theme hook to ignore
 * theme('breadcrumb'). Such a theme implementation will look much simpler than
 * the default imp.
 */
function theme_crumbs_breadcrumb(array $breadcrumb_items) {
  $original_page_title = drupal_get_title();
  $last_item = array_pop($breadcrumb_items);

  $breadcrumb_items_html = array();
  foreach ($breadcrumb_items as $item) {
    $breadcrumb_items_html[] = crumbs_render_breadcrumb_item($item);
  }

  // Some themes (Zen, in particular) use drupal_get_title() to
  // display the last breadcrumb item.
  // We use drupal_set_title() to temporarily override this value.
  drupal_set_title($last_item['link_title']);
  $breadcrumb_html = theme('breadcrumb', $breadcrumb_items_html);
  drupal_set_title($original_page_title);

  return $breadcrumb_html;
}


function crumbs_render_breadcrumb_item(array $item) {
  $options = $item['link_options'];
  return l($item['link_title'], $item['link_path'], $options);
}


function crumbs_build_breadcrumb(array $trail) {
  $pluginEngine = _crumbs_load_plugin_engine();
  $breadcrumbBuilder = new _crumbs_BreadcrumbBuilder($pluginEngine);
  return $breadcrumbBuilder->buildBreadcrumb($trail);
}


class _crumbs_BreadcrumbBuilder {

  protected $_pluginEngine;

  function __construct($pluginEngine) {
    $this->_pluginEngine = $pluginEngine;
  }

  function buildBreadcrumb($trail) {
    $breadcrumb = array();
    foreach ($trail as $path => $item) {
      if ($item) {
        $title = $this->_findTitle($path, $item, $breadcrumb);
        if (!isset($title)) {
          $title = $item['title'];
        }
        // The item will be skipped, if $title === FALSE.
        if (isset($title) && $title !== FALSE) {
          $item['link_title'] = $title;
          $item['link_options'] = array();
          $breadcrumb[] = $item;
        }
      }
    }
    $this->_decorateBreadcrumb($breadcrumb);
    return $breadcrumb;
  }

  protected function _findTitle($path, array $item, array $breadcrumb_parents) {
    $invokeAction = new _crumbs_InvokeAction_findTitle($path, $item, $breadcrumb_parents);
    $this->_pluginEngine->invokeAll_find($invokeAction);
    return $invokeAction->getValue();
  }

  protected function _decorateBreadcrumb(array &$breadcrumb) {
    $invokeAction = new _crumbs_InvokeAction_decorateBreadcrumb($breadcrumb);
    $this->_pluginEngine->invokeAll_alter($invokeAction);
  }
}


class _crumbs_InvokeAction_findTitle extends crumbs_InvokeAction_findForPath {

  protected $_method = 'findTitle';
  protected $_breadcrumb_parents;

  function __construct($path, array $item, array $breadcrumb_parents) {
    crumbs_InvokeAction_findForPath::__construct($path, $item);
    $this->_breadcrumb_parents = $breadcrumb_parents;
  }

  protected function _invoke($plugin, $method) {
    return $plugin->$method($this->_path, $this->_item, $this->_breadcrumb_parents);
  }
}


class _crumbs_InvokeAction_decorateBreadcrumb implements crumbs_InvokeActionInterface_alter {

  protected $_breadcrumb;

  function __construct(array &$breadcrumb) {
    $this->_breadcrumb = &$breadcrumb;
  }

  function invoke($plugin, $plugin_key) {
    if (method_exists($plugin, 'decorateBreadcrumb')) {
      $plugin->decorateBreadcrumb($this->_breadcrumb);
    }
  }
}



