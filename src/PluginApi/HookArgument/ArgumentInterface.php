<?php
namespace Drupal\crumbs\PluginApi\HookArgument;

use Drupal\crumbs\PluginApi\Family\FamilyInterface;

/**
 * Interface for the argument passed to hook_crumbs_plugins().
 */
interface ArgumentInterface extends FamilyInterface {

  /**
   * @return \Drupal\crumbs\PluginApi\Family\FamilyLoreInterface
   */
  function modulePluginFamily();

  /**
   * Register an entity route.
   * This should be called by those modules that define entity types and routes.
   *
   * @param string $entity_type
   * @param string $route
   * @param string $bundle_key
   * @param string $bundle_name
   */
  function entityRoute($entity_type, $route, $bundle_key, $bundle_name);

  /**
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * Overrides the parent method, to make the arguments optional.
   * (for backwards compatibility with older versions of Crumbs)
   *
   * @param string $key
   *   Rule key, relative to module name.
   * @param \crumbs_MonoPlugin $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function monoPlugin($key = NULL, \crumbs_MonoPlugin $plugin = NULL);

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * Overrides the parent method, to make the arguments optional.
   * (for backwards compatibility with older versions of Crumbs)
   *
   * @param string|null $key
   *   Rule key, relative to module name.
   * @param \crumbs_MultiPlugin|null $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key = NULL, \crumbs_MultiPlugin $plugin = NULL);
}
