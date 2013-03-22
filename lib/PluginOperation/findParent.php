<?php


class crumbs_PluginOperation_findParent extends crumbs_PluginOperation_findForPath {

  protected $method = 'findParent';

  protected function _invoke($plugin, $method) {
    $result = $plugin->$method($this->path, $this->item);
    return $result;
  }

  protected function _processValue($value) {
    if (url_is_external($value)) {
      // Always discard external paths.
      return NULL;
    }
    return drupal_get_normal_path($value);
  }
}
