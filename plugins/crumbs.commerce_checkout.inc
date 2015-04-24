<?php
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

/**
 * Implementation of hook_crumbs_plugins()
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function commerce_checkout_crumbs_plugins(ArgumentInterface $api) {

  // The parent path and title for checkout/%/% will be determined by the
  // commerce_checkout_CrumbsMonoPlugin_checkoutPages plugin.
  $api->routeMonoPlugin('checkout/%/%', 'checkout.checkoutPages', new commerce_checkout_CrumbsMonoPlugin_checkoutPages);
  $api->describeFindParent('checkoutPages', t('Commerce checkout pages'));
  $api->describeFindTitle('checkoutPages', t('Commerce checkout pages'));

  // The parent path for 'checkout/%' should always be 'cart'.
  $api->routeParentPath('checkout/%', 'checkout.cartToCheckout', 'cart');

  $api->disabledByDefault('checkout.*');
}

class commerce_checkout_CrumbsMonoPlugin_checkoutPages implements crumbs_MonoPlugin_FindParentInterface, crumbs_MonoPlugin_FindTitleInterface {

  /**
   * {@inheritdoc}
   */
  function findParent($path, $item) {
    // $item['map'][2] should contain the checkout step.
    if (empty($item['map'][2])) {
      return NULL;
    }
    // $item['map'][1] should contain the order.
    if (!is_object($item['map'][1])) {
      return NULL;
    }
    list(, $order, $page) = $item['map'];
    if (!empty($page['prev_page'])) {
      return 'checkout/' . $order->order_id . '/' . $page['prev_page'];
    }
    else {
      // If the step has no 'prev_page', then the parent is 'cart'.
      return 'cart';
    }
  }

  /**
   * {@inheritdoc}
   */
  function findTitle($path, $item) {
    if (empty($item['map'][2]['name'])) {
      return NULL;
    }
    if (!is_object($item['map'][1])) {
      return NULL;
    }
    list(, $order, $page) = $item['map'];
    return t($page['name']);
  }
}
