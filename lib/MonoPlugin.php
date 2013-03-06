<?php

/**
 * Interface for plugin objects registered with hook_crumbs_plugins().
 */
interface crumbs_MonoPlugin {

  /**
   * @param $api :crumbs_InjectedAPI_describeMonoPlugin
   *   Injected API object, with methods that allows the plugin to further
   *   describe itself.
   *
   * @return
   *   As an alternative to the API object's methods, the plugin can simply
   *   return a string label.
   */
  function describe($api);
}
