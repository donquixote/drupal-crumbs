<?php

/**
 * Implementation of hook_crumbs_plugins()
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function commerce_checkout_crumbs_plugins($api) {

  $api->routeMonoPlugin('checkout/%/%', 'checkoutPages');
  $api->routeParentPath('checkout/%', 'cartToCheckout', 'cart');
  $api->disabledByDefault('*');
}


class commerce_checkout_CrumbsMonoPlugin_checkoutPages implements crumbs_MonoPlugin_FindParentInterface, crumbs_MonoPlugin_FindTitleInterface {

  /**
   * @inheritdoc
   */
  function describe($api) {
    $api->titleWithLabel(t('Previous page in checkout process'), t('Parent'));
  }

  /**
   * @inheritdoc
   */
  function findParent($path, $item) {
    if (empty($item['map'][2])) {
      return;
    }
    if (!is_object($item['map'][1])) {
      return;
    }
    list(, $order, $page) = $item['map'];
    if (!empty($page['prev_page'])) {
      return 'checkout/' . $order->order_id . '/' . $page['prev_page'];
    }
    elseif ('checkout' === $page['page_id']) {
      return 'cart';
    }
  }

  /**
   * @inheritdoc
   */
  function findTitle($path, $item) {
    if (empty($item['map'][2]['name'])) {
      return;
    }
    if (!is_object($item['map'][1])) {
      return;
    }
    list(, $order, $page) = $item['map'];
    return t($page['name']);
  }
}