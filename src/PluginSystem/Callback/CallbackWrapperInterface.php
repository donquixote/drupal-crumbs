<?php

namespace Drupal\crumbs\PluginSystem\Callback;

interface CallbackWrapperInterface {

  /**
   * @param callable $callback
   * @param string $key
   *
   * @return \crumbs_MonoPlugin_FindParentInterface
   */
  public function wrapParentCallback($callback, $key);

  /**
   * @param callable $callback
   * @param string $key
   *
   * @return \crumbs_MonoPlugin_FindTitleInterface
   */
  public function wrapTitleCallback($callback, $key);

  /**
   * @param callable $callback
   * @param string $key
   *
   * @return \crumbs_EntityPlugin
   */
  public function wrapEntityParentCallback($callback, $key);

  /**
   * @param callable $callback
   * @param string $key
   *
   * @return \crumbs_EntityPlugin
   */
  public function wrapEntityTitleCallback($callback, $key);
}
