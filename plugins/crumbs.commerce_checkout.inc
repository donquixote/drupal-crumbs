<?php
use Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface;

/**
 * Implementation of hook_crumbs_plugins()
 *
 * @param \Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface $api
 */
function commerce_checkout_crumbs_plugins(ArgumentInterface $api) {

  $family = $api->modulePluginFamily()
    ->disabledByDefault();

  // The parent path and title for checkout/%/% will be determined by the
  // commerce_checkout_CrumbsMonoPlugin_checkoutPages plugin.
  $family->route('checkout/%/%')->monoPlugin('checkoutPages', new commerce_checkout_CrumbsMonoPlugin_checkoutPages)
    ->translateDescription('Commerce checkout pages');

  // The parent path for 'checkout/%' should always be 'cart'.
  $family->route('checkout/%')->fixedParentPath('cartToCheckout', 'cart');
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
