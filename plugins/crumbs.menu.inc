<?php


/**
 * Implementation of hook_crumbs_plugins()
 */
function menu_crumbs_plugins($api) {
  $api->multiPlugin('hierarchy');
  $api->multiPlugin('link_title');
}


class menu_CrumbsMultiPlugin_hierarchy implements crumbs_MultiPlugin {

  function describe($api) {
    return menu_get_menus();
  }

  function disabledByDefault() {
    return array('*');
  }

  function findParent($path, $item) {

    $q = db_select('menu_links', 'parent');
    $q->innerJoin('menu_links', 'child', 'parent.mlid = child.plid');
    $q->addExpression('parent.link_path', 'parent_path');
    $q->addExpression('parent.menu_name', 'menu_name');
    $q->condition('child.link_path', $path);

    // Top-level links have higher priority.
    $q->orderBy('parent.depth', 'ASC');

    // Ignore placeholder or separator menu items added by special_menu_items.
    $q->condition('parent.link_path', array('<nolink>', '<separator>'), 'NOT IN');

    // Collect candidates for the parent path, keyed by menu name.
    $candidates = array();
    foreach ($q->execute() as $row) {
      if (!isset($candidates[$row->menu_name])) {
        $candidates[$row->menu_name] = $row->parent_path;
      }
    }
    return $candidates;
  }
}


class menu_CrumbsMultiPlugin_link_title implements crumbs_MultiPlugin {

  function describe($api) {
    return menu_get_menus();
  }

  function disabledByDefault() {
    return array('*');
  }

  /**
   * Find all menu links with $path as the link path.
   * For each menu, find the one with the lowest depth.
   */
  function findTitle($path, $item) {

    $q = db_select('menu_links', 'ml');
    $q->fields('ml', array('link_title', 'menu_name'));
    $q->condition('link_path', $path);

    if (module_exists('i18n_menu')) {
      // i18n_menu needs some more fields to decide whether an item should be
      // translated with i18n_menu logic.
      $q->fields('ml', array('customized', 'language'));
    }

    // Top-level links have higher priority.
    $q->orderBy('ml.depth', 'ASC');

    // We need to load the original title from menu_router.
    $q->leftJoin('menu_router', 'mr', 'ml.router_path = mr.path');
    $q->fields('mr', array('title'));

    $titles = array();
    foreach ($q->execute() as $row) {
      if (!isset($titles[$row->menu_name])) {
        // Replicate the translation logic of _menu_item_localize(), which is
        // used by menu_link_load() via _menu_link_translate().
        if (
          // If i18n_menu is enabled, we replicate the logic of
          // i18n_menu_translated_menu_link_alter()
          module_exists('i18n_menu') &&
          // Only use menu_i18n for customized items
          !empty($row->custmomized) &&
          // Items that already have a language don't need to be translated
          empty($row->language)
        ) {
          // See _i18n_menu_link_title()
          $titles[$row->menu_name] = i18n_string_translate(
            array('menu', 'item', $link['mlid'], 'title'),
            $link['link_title'],
            array('langcode' => $langcode)
          );
        }
        else {
          // i18n_menu is not enabled or does not apply to this link, so we
          // replicate the logic of Drupal core menu link translation.
          // See _menu_item_localize().
          if ($row->link_title == $row->title) {
            // Use the localized title from menu_router.
            // Fortunately, this is already computed by menu_get_item().
            $titles[$row->menu_name] = $item['title'];
          }
          else {
            // Use the untranslated title from menu links.
            $titles[$row->menu_name] = $row->link_title;
          }
        }
      }
    }
    return $titles;
  }
}
