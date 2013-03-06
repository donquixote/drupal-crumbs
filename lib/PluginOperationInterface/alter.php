<?php

/**
 * Interface to be used internally by the plugin engine.
 */
interface crumbs_PluginOperationInterface_alter {
  function invoke($plugin, $plugin_key);
}
