<?php

/**
 * Implements hook_crumbs_plugins() on behalf of views.
 */
function views_crumbs_plugins($api) {
  // Let's start with a simple mono plugin.
  $api->monoPlugin();
}


class views_CrumbsMonoPlugin implements crumbs_MonoPlugin {

  protected $drupalBreadcrumb;

  function describe($api) {
    return t('Breadcrumb title for views paths.');
  }

  function findTitle($path, $item) {
    if ($item['page_callback'] === 'views_page') {
      $breadcrumb = $this->drupalBreadcrumb();
      $url = url($item['href']);
      dpm($item);
      if (isset($breadcrumb[$url])) {
        dpm($breadcrumb);
        dpm($url);
        return $breadcrumb[$url];
      }
    }
  }

  protected function drupalBreadcrumb() {
    if (!isset($this->drupalBreadcrumb)) {
      $drupal_breadcrumb = drupal_get_breadcrumb();
      $this->drupalBreadcrumb = array();
      if (is_array($drupal_breadcrumb)) {
        // Should we parse the link with regex or with DOM extension?
        // People say (and I agree) that regex is too weak a tool to parse html.
        // But for something as simple as a link tag?
        // http://stackoverflow.com/questions/3820666/grabbing-the-href-attribute-of-an-a-element/3820783#3820783
        $dom = new DOMDocument;
        $dom->loadHTML(implode('', $drupal_breadcrumb));
        foreach ($dom->getElementsByTagName('a') as $node) {
          $text = $node->nodeValue;
          $href = $node->getAttribute('href');
          // Strip any 'all' from the end of the href..
          while ('/all' === substr($href, -4)) {
            $href = substr($href, 0, -4);
          }
          $this->drupalBreadcrumb[$href] = $text;
        }
      }
    }
    return $this->drupalBreadcrumb;
  }
}
